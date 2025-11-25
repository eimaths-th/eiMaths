<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Mail\CoursePendingConfirmation;
use App\Services\TelegramService;
use App\Models\bookuse;
use App\Models\Contact;
use Illuminate\Http\Request;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\CourseLevel;
use Modules\Localization\Entities\Language;
use App\Models\CoursePending;
use App\Models\Department;
use App\Models\level;
use App\Models\LineApiSetting;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use App\Models\OrderRunningNumber;
use App\Models\Sterm;
use App\Models\studentRunningNumber;
use App\Models\term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\CourseSetting\Entities\Category;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use GuzzleHttp\Client;
use Svg\Tag\Rect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


// use Toastr;

class CourseController extends Controller
{
    public function coursePageSection(Request $request)
    {
        $query = Course::with([
            'user',
            'enrolls',
            'comments',
            'reviews',
            'lessons',
            'activeReviews',
            'enrollUsers',
            'cartUsers',
            'courseLevel',
        ])->where('scope', 1);

        // Type filtering
        $type = $request->input('type', '');
        if (!empty($type)) {
            $types = explode(',', $type);
            foreach ($types as $t) {
                if ($t === 'free') {
                    $query->where('price', 0);
                } elseif ($t === 'paid') {
                    $query->where('price', '>', 0);
                }
            }
        }

        // Language filtering
        $language = $request->input('language', '');
        if (!empty($language)) {
            $row_languages = explode(',', $language);
            $LanguageList = Language::whereIn('code', $row_languages)->get();
            $languages = $LanguageList->pluck('id')->toArray();
            $query->whereIn('lang_id', $languages);
        }

        // Mode filtering
        $mode = $request->input('mode', '');
        if (!empty($mode)) {
            $modes = explode(',', $mode);
            $query->whereIn('mode_of_delivery', $modes);
        }

        // Level filtering
        $level = $request->input('level', '');
        if (!empty($level)) {
            $levels = explode(',', $level);
            $query->whereIn('level', $levels);
        }

        // Required type (specific to Org module)
        if (isModuleActive('Org')) {
            $required_type_request = $request->input('required_type', '');
            if (!empty($required_type_request)) {
                $required_type = [];
                $types = explode(',', $required_type_request);
                foreach ($types as $type) {
                    if ($type === 'compulsory') {
                        $required_type[] = 1;
                    } elseif ($type === 'open') {
                        $required_type[] = 0;
                    }
                }
                $query->whereIn('required_type', $required_type);
            }
        }

        // Category filtering
        $category = $request->input('category', '');
        if (!empty($category)) {
            $categories = explode(',', $category);
            $query->whereIn('category_id', $categories);
        }

        // Sub-category filtering
        $subCategory = $request->input('sub-category');
        if (!empty($subCategory)) {
            $query->where('subcategory_id', $subCategory);
        }

        // Subject filtering (specific to 'tvt' theme)
        if (currentTheme() === 'tvt') {
            $subject = $request->input('subject');
            if (!empty($subject)) {
                $subjects = explode(',', $subject);
                $query->whereIn('school_subject_id', $subjects);
            }
        }

        $query->where('type', 1)->where('status', 1);

        // Order filtering
        $order = $request->input('order', '');
        if (currentTheme() === 'wetech') {
            if (empty($order)) {
                $query->latest();
            } else {
                if ($order === "title") {
                    $query->orderBy('title');
                } elseif ($order === "enroll") {
                    $query->orderBy('total_enrolled');
                } elseif ($order === "created_at") {
                    $query->orderBy('created_at');
                } elseif ($order === "end_date") {
                    $query->orderBy('required_type', 'desc');
                }
            }
        } else {
            if (empty($order)) {
                $query->orderBy('total_enrolled', 'desc');
            } else {
                if ($order === "price") {
                    $query->orderBy('price', 'desc');
                } else {
                    $query->latest();
                }
            }
        }

        $courses = $query->paginate(itemsGridSize());
        $total = $courses->total();
        $levels = CourseLevel::getAllActiveData();

        return view('affiliate.courses', compact(
            'levels',
            'mode',
            'category',
            'level',
            'order',
            'language',
            'type',
            'total',
            'courses'
        ));
    }

    public function courselList(Request $request)
    {
        $user = Auth::user();

        $video_list = [];
        $vdocipher_list = [];

        $courses = [];
        $query = Category::where('status', 1)->orderBy('position_order', 'ASC');
        $categories = $query->with('childs')->get();

        $languages = getLanguageList();
        $levels = CourseLevel::where('status', 1)->get(['title', 'id']);
        $title = 'All';


        $data['category_search'] = $request->get('category', '');
        $data['category_type'] = $request->get('type', '');
        $data['category_instructor'] = $request->get('instructor', '');
        $data['search_required_type'] = (int)$request->get('search_required_type', 0);
        $data['search_delivery_mode'] = $request->get('search_delivery_mode', '');
        $data['category_status'] = $request->get('search_status', '');


        $sub_lists = [];

        $query = Course::where('type', 1)->with('category', 'user');
        // dd($request);

        if ($request->course_status != "") {
            if ($request->course_status == 1) {
                $query->where('courses.status', 1);
            } elseif ($request->course_status == 0) {
                $query->where('courses.status', 0);
            }
        }

        if ($request->category != "") {
            $query->where('category_id', $request->category);
        }
        if ($request->type != "") {
            $query->where('type', $request->type);
        }
        if ($request->instructor != "") {
            $query->where('user_id', $request->instructor);
        }
        if ($request->search_status != "") {
            $query->where('courses.status', $request->search_status == "Active" ? 1 : 0);
        }
        if ($request->required_type != "") {
            $query->where('required_type', $request->required_type == 'Compulsory' ? 1 : 0);
        }
        if ($request->mode_of_delivery != "") {
            $query->where('mode_of_delivery', $request->mode_of_delivery);
        }
        // dd($request);

        if ($request->ajax()) {

            $query->select('courses.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('title', function ($query) {
                    return $query->title;
                })
                ->editColumn('required_type', function ($query) {
                    return $query->required_type == 1 ? trans('courses.Compulsory') : trans('courses.Open');
                })->editColumn('mode_of_delivery', function ($query) {
                    if ($query->mode_of_delivery == 1) {
                        // $title = trans('courses.Online');
                        $title = 'Online';
                    } elseif ($query->mode_of_delivery == 2) {
                        $title = 'Distance Learning';

                        // $title = trans('courses.Distance Learning');
                    } else {
                        if (isModuleActive('Org')) {
                            $title = 'Offline';
                        } else {
                            $title = 'Face-to-Face';
                        }
                    }
                    return $title;
                })
                ->addColumn('type', function ($query) {
                    return $query->type == 1 ? 'Course' : 'Quiz';
                })->addColumn('status', function ($query) {
                    // return 1;

                    return view('components._course_status_td', ['query' => $query]);
                })
                // ->addColumn('lessons', function ($query) {
                //     return translatedNumber($query->lessons->count());
                // })
                ->editColumn('category', function ($query) {
                    if ($query->category) {
                        return $query->category->name;
                    } else {
                        return 'erro category';
                    }
                })
                // ->editColumn('quiz', function ($query) {
                //     if ($query->quiz) {
                //         return $query->quiz->title;
                //     } else {
                //         return '';
                //     }
                // })
                ->editColumn('user', function ($query) {
                    if ($query->user) {
                        return $query->user->name;
                    } else {
                        return 'erro user';
                    }
                })
                // ->addColumn('enrolled_users', function ($query) {
                //     return translatedNumber($query->enrollUsers->where('teach_via', 1)->count()) . "/" . translatedNumber($query->enrollUsers->where('teach_via', 2)->count());
                // })
                ->editColumn('scope', function ($query) {
                    if ($query->scope == 1) {
                        $scope = 'Public';
                    } else {
                        $scope = 'Private';
                    }
                    return $scope;
                })->addColumn('price', function ($query) {
                    return number_format($query->price) . ' ฿';
                    // return view('coursesetting::components._course_price_td', ['query' => $query]);
                })->addColumn('action', function ($query) {

                    $html = '<a class="btn btn-warning edit-btn" data-id=" ' . $query->id . '">Edit</a> ';
                    // return $html;
                    return view('components._course_action_td', ['query' => $query]);

                    // return '1';
                })->rawColumns(['status', 'price', 'action', 'enrolled_users'])
                ->make(true);
        }
        // dd(
        //     $languages,
        //     $categories,
        // );
        return view(
            'course.course_list',
            $data,
            compact(
                'sub_lists',
                'levels',
                // 'video_list',
                // 'vdocipher_list',
                'title',
                // 'quizzes',
                'courses',
                'categories',
                'languages',
                // 'instructors'
            )
        );
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'mode_of_delivery' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required|boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Save the image
        $today = Carbon::now()->format('d-m-Y');
        $folderPath = "uploads/main/files/{$today}";
        $imagePath = '';
        // Store the uploaded image
        if ($request->hasFile('image')) {
            if (!file_exists(public_path($folderPath))) {
                mkdir(public_path($folderPath), 0777, true);
            }
            $fileName = Str::random(10) . '-' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path($folderPath), $fileName);
        }
        // dd($folderPath, $request->all());
        $imagePath = 'public/' . $folderPath . '/' . $fileName;

        $addCourse = new Course;
        $addCourse->category_id = $request->input('category_id');
        $addCourse->user_id = Auth::id();
        $addCourse->lang_id = $request->input('lang_id');
        $addCourse->title = $request->input('title');
        $addCourse->slug = $request->input('title');
        $addCourse->image = $imagePath; // Path after storing the image
        $addCourse->thumbnail = $imagePath; // Path after storing the image
        $addCourse->price = $request->input('price');

        $addCourse->publish = $request->input('publish'); // Assuming radio button values are 1 or 0
        $addCourse->status = $request->input('status'); // Assuming radio button values are 1 or 0
        $addCourse->level = $request->input('level'); // Assuming radio button values are 1 or 0
        $addCourse->type = $request->input('type'); // Assuming radio button values are 1 or 0
        $addCourse->scope = $request->input('scope'); // Assuming radio button values are 1 or 0
        $addCourse->mode_of_delivery = $request->input('mode_of_delivery'); // Assuming radio button values are 1 or 0
        $addCourse->show_mode_of_delivery = $request->input('show_mode_of_delivery'); // Assuming radio button values are 1 or 0



        // Save the course
        $addCourse->save();

        return response()->json(['success' => true, 'message' => 'Course added successfully.']);
    }

    public function edit(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        return response()->json(['success' => true, 'course' => $course]);
    }

    public function destroy($id)
    {
        // Find the course by ID
        $course = Course::findOrFail($id);

        // Get the image path from the course model
        $imagePath = asset(str_replace('public/', '', $course->image)); // Assuming the image path is stored in the `image` attribute of the course

        // Delete the image file from the server (if it exists)
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image file
        }

        // Attempt to delete the course
        if ($course->delete()) {
            return response()->json(['success' => true, 'message' => 'Course and image deleted successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error deleting the course and image.']);
        }
    }
    public function update(Request $request, $id)
    {

        // dd($request->all());
        $validated = $request->validate([
            'title' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'mode_of_delivery' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required|boolean',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add image validation
        ]);
        $course = Course::findOrFail($id);
        $oldPath = $course->image;
        $imagePath = $oldPath;
        // Store the uploaded image
        if ($request->hasFile('image')) {
            if (file_exists(public_path($oldPath)) && $oldPath != 'default_image_path') {
                unlink(public_path($oldPath));  // Delete old image file
            }

            $today = Carbon::now()->format('d-m-Y');
            $folderPath = "uploads/main/files/{$today}";
            if (!file_exists(public_path($folderPath))) {
                mkdir(public_path($folderPath), 0777, true);
            }
            $fileName = Str::random(10) . '-' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path($folderPath), $fileName);
            $imagePath = 'public/' . $folderPath . '/' . $fileName;
        }

        $course->category_id = $request->input('category_id');
        $course->user_id = Auth::id();
        $course->lang_id = $request->input('lang_id');
        $course->title = $request->input('title');
        $course->slug = $request->input('title');
        $course->image = $imagePath;
        $course->thumbnail = $imagePath;
        $course->price = $request->input('price');
        $course->publish = $request->input('publish');
        $course->status = $request->input('status');
        $course->level = $request->input('level');
        $course->type = $request->input('type');
        $course->scope = $request->input('scope');
        $course->mode_of_delivery = $request->input('mode_of_delivery');
        $course->show_mode_of_delivery = $request->input('show_mode_of_delivery');
        // Save the course
        $course->save();

        return response()->json(['success' => true, 'message' => 'Course updated successfully.']);
    }
    public function categories()
    {
        $query = Category::query();

        $categories = $query->with('parent')->orderBy('position_order', 'asc')->get();
        $max_id = count($categories) + 1;

        // dd($categories);

        return view('course.category.category-list', compact('categories', 'max_id'));
    }

    public function categoriesStore(Request $request)
    {

        $code = app()->getLocale();
        // dd($code);
        $rules = [
            'name.' . $code => 'required|max:255',
        ];
        // dd($request, $code,$rules);

        // $this->validate($request, $rules, validationMessage($rules));
        DB::beginTransaction();

        $check_position = Category::where('position_order', $request->position_order)->first();

        if ($check_position != '') {
            $old_categories = Category::where('position_order', '>=', $request->position_order)->get();

            foreach ($old_categories as $old_category) {
                $old_category->position_order = $old_category->position_order + 1;
                $old_category->save();
            }
        }


        $store = new Category;

        foreach ($request->name as $key => $name) {
            $store->setTranslation('name', $key, $name);
        }
        foreach ($request->description as $key => $description) {
            $store->setTranslation('description', $key, $description);
        }
        $store->status = $request->status;
        if (!empty($request->parent)) {
            $store->parent_id = $request->parent;
        } else {
            $store->parent_id = null;
        }
        $store->position_order = $request->position_order;
        $store->color = $request->color;

        $store->user_id = Auth::id();
        $store->save();

        DB::commit();
        Toastr::success(trans('common.Operation successful'), trans('common.Success'));
        return redirect()->back();
    }

    public function categoriesEdit(Request $request, $id)
    {
        $edit = Category::find($id);
        $query = Category::orderBy('position_order', 'ASC');

        $categories = $query->with('parent')->orderBy('position_order', 'asc')->get();
        $max_id = count($categories) + 1;
        return view('course.category.category-list', compact('categories', 'edit', 'max_id'));
    }

    public function categoriesUpdate(Request $request)
    {

        // dd($request);
        $code = auth()->user()->language_code;

        $rules = [
            'name.' . $code => 'required|max:255',
        ];

        $this->validate($request, $rules, validationMessage($rules));


        $is_exist = Category::where('name', $request->name)->where('id', '!=', $request->id)->first();
        if ($is_exist) {
            Toastr::error(trans('frontend.This name has been already taken'), trans('common.Failed'));
            return redirect()->back();
        }

        $check_position = Category::where('position_order', $request->position_order)->first();

        if ($check_position != '') {
            $old_categories = Category::where('position_order', '>=', $request->position_order)->get();

            foreach ($old_categories as $old_category) {
                $old_category->position_order = $old_category->position_order + 1;
                $old_category->save();
            }
        }

        $store = Category::find($request->id);
        foreach ($request->name as $key => $name) {
            $store->setTranslation('name', $key, $name);
        }
        foreach ($request->description as $key => $description) {
            $store->setTranslation('description', $key, $description);
        }
        $store->status = $request->status;
        $store->url = $request->url;
        $store->title = $request->title;
        $store->show_home = (int)$request->show_home;
        $store->position_order = $request->position_order;
        $store->color = $request->color;

        if (!empty($request->parent)) {
            $store->parent_id = $request->parent;
        } else {
            $store->parent_id = null;
        }
        $store->image = null;
        $store->thumbnail = null;
        $store->save();


        // $category = Category::findOrFail($request->id);
        // foreach ($request->name as $key => $value) {
        //     $category->setTranslation('name', $key, $value);
        // }
        // foreach ($request->description as $key => $value) {
        //     $category->setTranslation('description', $key, $value);
        // }
        // $category->status = $request->status;
        // $category->save();

        Toastr::success('Operation successful', 'Success');
        return redirect()->route('categories.index');
    }

    public function categoriesDelete($id)
    {
        $category = Category::find($id);
        $check_position = Category::where('position_order', $category->position_order)->first();

        if ($check_position != '') {
            $old_categories = Category::where('position_order', '>=', $category->position_order)->get();

            foreach ($old_categories as $old_category) {
                $old_category->position_order = $old_category->position_order - 1;
                $old_category->save();
            }
        }

        if ($category) {
            $category->delete(); // Deletes the category
            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
    }

    public function getAllCourseData(Request $request)
    {

        if ($request->ajax()) {

            $query = Course::whereIn('type', [1, 2])->with('category', 'user');
            if ($request->course_status != "") {
                if ($request->course_status == 1) {
                    $query->where('courses.status', 1);
                } elseif ($request->course_status == 0) {
                    $query->where('courses.status', 0);
                }
            }
            if ($request->category != "") {
                $query->where('category_id', $request->category);
            }
            if ($request->type != "") {
                $query->where('type', $request->type);
            }
            if ($request->instructor != "") {
                $query->where('user_id', $request->instructor);
            }
            if ($request->search_status != "") {
                $query->where('courses.status', $request->search_status == "Active" ? 1 : 0);
            }
            if ($request->required_type != "") {
                $query->where('required_type', $request->required_type == 'Compulsory' ? 1 : 0);
            }
            if ($request->mode_of_delivery != "") {
                $query->where('mode_of_delivery', $request->mode_of_delivery);
            }

            $query->select('courses.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('title', function ($query) {
                    return $query->title;
                })
                ->editColumn('required_type', function ($query) {
                    return $query->required_type == 1 ? trans('courses.Compulsory') : trans('courses.Open');
                })->editColumn('mode_of_delivery', function ($query) {
                    if ($query->mode_of_delivery == 1) {
                        $title = trans('courses.Online');
                    } elseif ($query->mode_of_delivery == 2) {
                        $title = trans('courses.Distance Learning');
                    } else {
                        if (isModuleActive('Org')) {
                            $title = trans('courses.Offline');
                        } else {
                            $title = trans('courses.Face-to-Face');
                        }
                    }
                    return $title;
                })
                ->addColumn('type', function ($query) {
                    return $query->type == 1 ? trans('courses.Course') : trans('quiz.Quiz');
                })->addColumn('status', function ($query) {
                    return view('coursesetting::components._course_status_td', ['query' => $query]);
                })->addColumn('lessons', function ($query) {
                    return translatedNumber($query->lessons->count());
                })
                ->editColumn('category', function ($query) {
                    if ($query->category) {
                        return $query->category->name;
                    } else {
                        return '';
                    }
                })
                ->editColumn('quiz', function ($query) {
                    if ($query->quiz) {
                        return $query->quiz->title;
                    } else {
                        return '';
                    }
                })->editColumn('user', function ($query) {
                    if ($query->user) {
                        return $query->user->name;
                    } else {
                        return '';
                    }
                })->addColumn('enrolled_users', function ($query) {
                    return translatedNumber($query->enrollUsers->where('teach_via', 1)->count()) . "/" . translatedNumber($query->enrollUsers->where('teach_via', 2)->count());
                })
                ->editColumn('scope', function ($query) {
                    if ($query->scope == 1) {
                        $scope = trans('courses.Public');
                    } else {
                        $scope = trans('courses.Private');
                    }
                    return $scope;
                })->addColumn('price', function ($query) {
                    return view('coursesetting::components._course_price_td', ['query' => $query]);
                })->addColumn('action', function ($query) {
                    return view('coursesetting::components._course_action_td', ['query' => $query]);
                })->rawColumns(['status', 'price', 'action', 'enrolled_users'])
                ->make(true);
        }
        return view('question.qusetion-list');
    }

    public function updateStatus(Request $request)
    {

        try {

            $course = Course::find($request->id); // Find the course by ID

            $course->status = $request->status; // Update the status

            $course->save(); // Save the changes

            // dd($request->all(), $request->status , $course);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }

    public function courseLevelList(Request $request)
    {

        $levels = CourseLevel::all();

        return view('course.levels.course_level_list', compact('levels'));
    }

    public function category_status_update(Request $request)
    {
        // dd($request);

        try {
            $store = Category::find($request->id);
            $store->status = $request->status;
            $store->save();
            return response()->json([
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function courseLevelStore(Request $request)
    {
        try {
            $level = new CourseLevel();
            $level->id = CourseLevel::max('id') + 1;
            foreach ($request->title as $key => $title) {
                $level->setTranslation('title', $key, $title);
            }
            $level->save();

            return response()->json([
                'success' => true,
                'message' => 'Course level added successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add course level.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function courseLevelUpdateStatus(Request $request)
    {
        // dd($request);
        try {
            $store = CourseLevel::find($request->id);
            $store->status = $request->status;
            $store->save();
            return response()->json([
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function courseLevelEdit(Request $request, $id)
    {
        $edit = CourseLevel::find($id);
        $levels = CourseLevel::all();

        return view('course.levels.course_level_list', compact('levels', 'edit'));
    }

    public function courseLevelUpdate(Request $request)
    {
        $code = auth()->user()->language_code;

        // Validate the request
        $validated = $request->validate([
            'title.' . $code => 'required|max:255',  // Validate the 'title' field for the specific language code
        ]);

        $edit = CourseLevel::findOrFail($request->id);
        foreach ($request->title as $key => $title) {
            $edit->setTranslation('title', $key, $title);
        }
        $edit->save();

        // Return a success response
        return response()->json(['success' => true, 'message' => 'Course level updated successfully.']);
    }

    public function courseLevelDestroy($id)
    {

        try {
            $level = CourseLevel::findOrFail($id);
            $level->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course level deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting course level: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCourseLevelsData()
    {
        $levels = CourseLevel::select(['id', 'title', 'status']); // Add your fields here
        return response()->json([
            'data' => $levels
        ]);
    }

    public function coursesPendingIndex(Request $request)
    {
        $centre = Department::where([['status', '1']])->orderBy("name", "asc")->get();
        $term = term::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $sterm = Sterm::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $bookuse = bookuse::where([['status', '1']/* , ['type', '1'] */])
            ->orderBy("name", "asc")->get();
        $level = level::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        if ($request->ajax()) {
            // $query = CoursePending::where('start_course', 1)->orderBy('id', 'desc');
            $query = CoursePending::query()->orderBy('id', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('ref', function ($query) {
                    return $query->ref ?? '';
                })
                ->editColumn('name', function ($query) {
                    return $query->name ?? '';
                })->editColumn('email', function ($query) {

                    return $query->email ?? '';
                })
                ->addColumn('telp', function ($query) {

                    return $query->telp ?? '';
                })->addColumn('type_parent', function ($query) {

                    return $query->type_parent ?? '';
                })
                ->editColumn('department', function ($query) {
                    return $query->department->code ?? '';
                })
                ->editColumn('appointment_date ', function ($query) {
                    return $query->appointment_date ?? '';
                })
                ->editColumn('student_name', function ($query) {

                    $name = '';
                    if ($query->student_name) {
                        $name = $query->student_name;
                    }
                    if ($query->student_nickname) {
                        $name .= ' ( ' . $query->student_nickname . ' )';
                    }
                    return $name;
                })
                ->editColumn('grade', function ($query) {
                    return $query->grade ?? '';
                })
                ->editColumn('status', function ($query) {
                    $html = '';
                    if ($query->status == 1) {
                        # code...
                        $html .= '<span class="badge bg-warning rounded-pill"> pending </span>';
                    } elseif ($query->status == 2) {
                        # code...
                        $html .= '<span class="badge bg-success rounded-pill"> Accepted </span>';
                    } elseif ($query->status == 3) {
                        # code...
                        $html .= '<span class="badge bg-success rounded-pill"> Waiting for Payment </span>';
                    } elseif ($query->status == 4) {
                        # code...
                        $html .= '<span class="badge bg-success rounded-pill"> success </span>';
                    } elseif ($query->status == 0) {
                        # code...
                        $html .= '<span class="badge bg-danger rounded-pill"> denie </span>';
                    }
                    return $html;
                })

                ->addColumn('action', function ($query) {

                    $html = '';
                    $btn = '<button class="btn btn-primary btn-sm view-detail-btn" data-id="' . $query->id . '" style="margin-right: 5px;"> <i class="fa fa-eye"></i> </button>';
                    $dropdown = '<div class="dropdown" style="display: inline-block;">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown' . $query->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown' . $query->id . '">';

                    if ($query->status == 1) {
                        $dropdown .= '<li><button class="dropdown-item confirm-btn" data-id="' . $query->id . '">Confirm</button></li>
                                      <li><button class="dropdown-item cancel-btn" data-id="' . $query->id . '">Cancel</button></li>';
                    } elseif ($query->status == 2) {
                        $dropdown .= '<li><button class="dropdown-item ModalNewStd" data-id="' . $query->id . '">New Student</button></li>
                                      <li><button class="dropdown-item ModalNewReciept" data-id="' . $query->id . '">New Receipt</button></li>
                                      <li><button class="dropdown-item reset-btn" data-id="' . $query->id . '">Reset</button></li>';
                    } elseif ($query->status == 0) {
                        $dropdown .= '<li><button class="dropdown-item reset-btn" data-id="' . $query->id . '">Reset</button></li>';
                    } else {
                        # code...
                        // $dropdown .= '';
                        $dropdown .= '<li><button class="dropdown-item reset-btn" data-id="' . $query->id . '">Reset</button></li>';
                    }
                    $dropdown .= '<li><button class="dropdown-item view-slip-btn" data-id="' . $query->id . '">View Slip</button></li>';
                    // $dropdown .= '<li><button class="dropdown-item view-detail-btn" data-id="' . $query->id . '">View Details</button></li>';
                    $dropdown .= '</ul>
                                </div>';
                    $html .= $btn . $dropdown;

                    return $html;
                })

                // ->addColumn('action', function ($query) {
                //     if ($query->status == 1) {
                //         return '<button class="btn btn-success confirm-btn btn-sm" data-id="' . $query->id . '">Confirm</button>
                //                 <button class="btn btn-danger cancel-btn btn-sm" data-id="' . $query->id . '">Cancel</button>';
                //     } elseif ($query->status == 2) {

                //         return '
                //         <button class="btn btn-success ModalNewStd btn-sm" data-id="' . $query->id . '">New Studnt</button>
                //         <button class="btn btn-success ModalNewReciept btn-sm" data-id="' . $query->id . '">New Reciept</button>
                //         <button class="btn btn-primary reset-btn btn-sm" data-id="' . $query->id . '">Reset</button>
                //         ';
                //     } elseif ($query->status == 0) {

                //         return '
                //         <button class="btn btn-primary reset-btn btn-sm" data-id="' . $query->id . '">Reset</button>
                //         ';
                //     }
                //     return '

                //     ';
                // })

                // ->addColumn('action', function ($query) {

                //     // $html = '<a class="btn btn-warning edit-btn" data-id=" ' . $query->id . '">Edit</a> ';
                //     // return $html;
                //     // return view('components._course_action_td', ['query' => $query]);
                //     // return '1';
                // })
                ->rawColumns(['status', 'price', 'action', 'enrolled_users'])
                ->make(true);
        }
        return view('course.courses_pending_index')
            ->with(['centre' => $centre])
            ->with(['bookuse' => $bookuse])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm])
        ;
    }


    public function fetchSlip($id)
    {
        // Assuming you have a CoursePending model that stores the slip image path
        $coursePending = CoursePending::find($id);

        if (!$coursePending) {
            return response()->json(['error' => 'Slip not found'], 404);
        }

        // Return the path to the image stored in the public directory
        return response()->json([
            'image' => asset($coursePending->slip) // Adjust path as needed
        ]);
    }
    public function coursesPendingConfirm($id)
    {
        $data = CoursePending::find($id);

        // Check if the course pending data exists
        if ($data) {
            $data->update(['status' => 2]); // Update the status to 2 (Confirm)
            return response()->json([
                'success' => true,
                'message' => 'Course status confirmed successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Course not found.',
        ]);
    }

    public function coursesPendingCancel($id)
    {
        $data = CoursePending::find($id);

        // Check if the course pending data exists
        if ($data) {
            $data->update(['status' => 0]); // Update the status to 3 (Cancel)
            return response()->json([
                'success' => true,
                'message' => 'Course status canceled successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Course not found.',
        ]);
    }

    public function coursesPendingReset($id)
    {
        $data = CoursePending::find($id);

        // Check if the course pending data exists
        if ($data) {
            $data->update(['status' => 1]); // Update the status to 3 (Cancel)
            return response()->json([
                'success' => true,
                'message' => 'Course status Reset successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Course not found.',
        ]);
    }

    public function coursesPendingStore(Request $request)
    {
        $validatedData = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'telp' => 'required|string|max:15',
            'appointment_date' => 'required|date_format:Y-m-d\TH:i',
            'student_name' => 'required|string|max:255',
            'student_nickname' => 'required|string|max:255',
            'grade' => 'required|string|max:10',
            'course_name' => 'required|string',
            'type_parent' => 'required|in:father,mother',
            'department_id' => 'required|string|max:255',
            'day' => 'required|string|max:255',
            'period' => 'required|string|max:255',
            'price' => 'required|string|max:255',

        ]);
        $validatedData['ref'] = $request->ref ?? '';
        $validatedData['status'] = '1';


        $create = CoursePending::create($validatedData);
        //line api test
        // $message = "New Course Pending Created:\n"
        //     . "Ref: {$validatedData['ref']}\n"
        //     . "Name: {$validatedData['name']}\n"
        //     . "Email: {$validatedData['email']}\n"
        //     . "Tel: {$validatedData['telp']}\n"
        //     . "Course Name: {$validatedData['course_name']}\n"
        //     . "Type Parent: {$validatedData['type_parent']}\n";

        // // Send a message to Line OA
        // // Replace with your Line User ID

        // // $lineUserId = 'U25fc5496e631e0cab0923f8f3cfc6db0';
        // // $accessToken = '2U8EhhLV69SeO/s/++xinSMV/g5r0K0WVvsaboSDAfGTX1Yz3820Xy/f7zSSz5wD+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMplrGoUWcLNE4NicviIUIcovVQ7Yub4BWefq7ABdgsr9QQdB04t89/1O/w1cDnyilFU='; // Replace with your Channel Access Token

        // $line_api = LineApiSetting::where('status', 1)->first();
        // if (!$line_api) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No active Line API settings found.',
        //     ], 404);
        // }

        // try {
        //     $lineUserId = decrypt($line_api->line_user_id);
        //     $accessToken = decrypt($line_api->channel_access_token);
        //     dd(
        //         $request->all(),
        //         $line_api,
        //         $accessToken,
        //         $lineUserId
        //     );

        //     $this->sendLineMessage($lineUserId, $accessToken, $message);
        // } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to decrypt Line API settings.',
        //     ], 500);
        // }

        // $lineUserId = decrypt($line_api->line_user_id);
        // $accessToken = decrypt($line_api->channel_access_token);

        // $this->sendLineMessage($lineUserId, $accessToken, $message);
        // disable for testing

        // $this->sendMulticastMessage([$lineUserId], $accessToken, 'Hello from Line OA!1cd ');

        // // For broadcast
        // $this->sendBroadcastMessage($accessToken, 'Hello from Line OA! 2');
        // $this->sendMessageToMyself($accessToken, $message);
        // dd($request->all(),$validatedData,$create);

        $qrCodePayment = generateQrCode($create->price);
        // dd($qrCodePayment);

        // ส่ง email ขอบคุณลูกค้า
        try {
            Mail::to($validatedData['email'])->send(new CoursePendingConfirmation($validatedData));
            Log::info('Course pending confirmation email sent to: ' . $validatedData['email']);
        } catch (\Exception $e) {
            Log::error('Failed to send course pending confirmation email: ' . $e->getMessage());
        }

        // ส่ง Telegram notification แจ้ง admin
        try {
            $department = Department::find($validatedData['department_id']);
            $validatedData['department_id'] = $department->name;
            $telegramService = new TelegramService();
            $telegramService->sendCoursePendingNotification($validatedData);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Course stored successfully!',
            'qrCodePayment' => $qrCodePayment,
            'coursePending_id' => $create->id,
        ]);
    }

    public function uploadSlip(Request $request)
    {
        $request->validate([
            'payment_slip' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'coursePending_id' => 'required',
        ]);


        // Find the CoursePending record
        $coursePending = CoursePending::find($request->coursePending_id);
        if (!$coursePending) {
            return response()->json(['success' => false, 'message' => 'Course pending not found.'], 404);
        }

        // Handle the file upload
        $folderPath = "img/payment_slips";
        $imagePath = '';

        if ($request->hasFile('payment_slip')) {
            // dd( $request->all(),$coursePending);

            $fileName = time() . Str::random(10) . '-' . $request->file('payment_slip')->getClientOriginalName();

            $request->file('payment_slip')->move(public_path($folderPath), $fileName);
            $imagePath =  $folderPath . '/' . $fileName;

            $coursePending->update(['slip' => $imagePath]);
            // dd( $request->all(),$coursePending);

            return response()->json([
                'success' => true,
                'message' => 'Slip uploaded successfully',
                'coursePending_id' => $coursePending->id // Return the file URL
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }

    public function senMail(Request $request)
    {

        // dd($request->all());
        $coursePending = CoursePending::find($request->coursePending_id);
        $coursePending_id = $request->coursePending_id;
        if (!$coursePending) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }
        $data = [
            'name' => $coursePending->name,
            'email' => $coursePending->email,
            'telp' => $coursePending->telp,
            'course_name' => $coursePending->course_name,
            'type_parent' => $coursePending->type_parent,
            'department' => $coursePending->department,
            'day' => $coursePending->day,
            'period' => $coursePending->period,
            'price' => $coursePending->price,
            'ref' => $coursePending->ref,
            'status' => $coursePending->status,
        ];

        Mail::to($data['email'])->send(new RegistrationConfirmation($data, $coursePending_id));
        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully.',
        ]);
    }


    private function sendLineMessage($lineUserId, $accessToken, $message)
    {
        $accessToken = '2U8EhhLV69SeO/s/++xinSMV/g5r0K0WVvsaboSDAfGTX1Yz3820Xy/f7zSSz5wD+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMplrGoUWcLNE4NicviIUIcovVQ7Yub4BWefq7ABdgsr9QQdB04t89/1O/w1cDnyilFU='; // Replace with your Channel Access Token

        $client = new Client();
        $url = 'https://api.line.me/v2/bot/message/push';

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            // Handle error if needed
            Log::error('Failed to send message to Line OA', ['response' => $response->getBody()->getContents()]);
        }
    }

    public function coursesPendingNewStd($id)
    {
        $data = CoursePending::find($id);

        $centre = Department::where([['status', '1']])->orderBy("name", "asc")->get();

        $htmlOptions = '';
        foreach ($centre as $dept) {
            $htmlOptions .= '<option value="' . $dept->id . '"' . ($data->department_id == $dept->id ? 'selected' : '') . '>' . $dept->name . '</option>';
        }
        $centre_id = $data->department_id;

        return response()->json([
            'success' => true,
            'data' => $data,
            'centre_id' => $centre_id,
            'htmlOptions' => $htmlOptions,
        ]);
    }

    public function getCheckRefStudnet(Request $request)
    {


        $student_id = $request->student_id[0];
        $coursePending = CoursePending::find($request->id);
        $student = Contact::find($student_id);

        $student_ref = $student->ref;
        $coursePending_ref = $coursePending->ref;
        $ref = '';
        if ($student_ref && ($student_ref == $coursePending_ref)) {
            $ref = $student->ref;
        } else if ($student_ref && ($student_ref != $coursePending_ref)) {
            # code...
            $ref = $student->ref;
            $coursePending->update(['status' => 0]);

            return response()->json([
                'success' => false,
                'message' => 'นักเรียนคนนี้มี Referent Code อยู่แล้ว',
                'ref' => $ref,
            ]);
        } else if ($student_ref == null) {
            # code...
            $ref = $coursePending->ref;
            $student->update(['ref' => $coursePending->ref]);
        }



        // dd($data);
        return response()->json([
            'success' => true,
            'ref' => $ref,
        ]);
    }


    public function updateWaitingForPayment($status, $id)
    {

        $data = CoursePending::find($id);
        if ($status == 'wait') {
            # code...
            $data->update(['status' => 3]);
        }
    }
    public function coursesPendingNewStdStore(Request $request)
    {
        // dd($request);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:contacts',
            'start_date' => 'required|string|max:10',
            //'start_term' => 'required',
            'school' => 'required|string|max:255',
            'level' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'father_name' => 'nullable|string|max:255|required_without:mother_name',
            //'father_email' => 'nullable|string|max:255|required_with:father_name,father_mobile',
            'father_mobile' => 'nullable|string|max:20|required_with:father_name',
            'mother_name' => 'nullable|string|max:255|required_without:father_name',
            //'mother_email' => 'nullable|string|max:255|required_with:mother_name,mother_mobile',
            'mother_mobile' => 'nullable|string|max:20|required_with:mother_name',
        ], [
            'name.required' => 'Student name must not be empty.',
            'name.unique' => 'This student name already exists in the database.',
            'start_date.required' => 'Please provide a start date.',
            'start_term.required' => 'Please select a start term.',
            'school.required' => 'Please provide the school name.',
            'level.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
            'father_name.required_without' => 'Either Father name or Mother name must be provided.',
            'father_email.required_with' => 'Father email is required when Father name is provided.',
            'father_mobile.required_with' => 'Father mobile is required when Father name is provided.',
            'mother_name.required_without' => 'Either Mother name or Father name must be provided.',
            'mother_email.required_with' => 'Mother email is required when Mother name is provided.',
            'mother_mobile.required_with' => 'Mother mobile is required when Mother name is provided.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $bookq = bookuse::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $mbprice = $levelq->book_price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $mbprice = $levelq->book_half_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($request->get('level2'))) {

            $bookq2 = bookuse::find($request->get('bookuse2'));
            $levelq2 = Level::find($request->get('level2'));
            $termq2 = Term::find($request->get('term2'));

            $mprice = 0;
            $mbprice = 0;
            $coin = 0;
            for ($i = $levelq->id; $i <= $levelq2->id; $i++) {
                $sbook = 1;

                if ($i == $levelq->id) {
                    $sterm = $termq->name;
                } else {
                    $sterm = 1;
                }

                if ($i == $levelq2->id) {
                    $eterm = $termq2->name;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    if ($i == $levelq->id && $j == $sterm) {
                        $sbook = $bookq->type;
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $sbook = $bookq->type;
                    } else {
                        $sbook = 1;
                    }
                    //echo $j . "<br>";
                    /*  $books = bookuse::where('type', '=', $sbook)
                        ->where('level_id', '=', $i)
                        ->where('term_id', '=', $j)
                        ->get(); */
                    $books = bookuse::select(
                        "bookuses.*",
                        "bookuses.type as level_type",
                        "levels.name as level_name",
                        "levels.price as price",
                        "levels.half_price as half_price",
                        "levels.book_price as book_price",
                        "levels.book_half_price as book_half_price",
                        "terms.name as term_name",
                    )
                        ->join("terms", "terms.id", "=", "bookuses.term_id")
                        ->join("levels", "levels.id", "=", "bookuses.level_id");

                    if ($i == $levelq->id && $j == $sterm) {
                        $books->where('bookuses.id', '=', $bookq->id);
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $books->where('bookuses.id', '=', $bookq2->id);
                    } else {
                        $books->where('type', '=', $sbook)->where('bookuses.level_id', '=', $i)
                            ->where('bookuses.term_id', '=', $j);
                    }
                    $books = $books->get();
                    $bookss[] = $books;

                    if ($books[0]->level_type == 1) {
                        $mprice = $mprice + $books[0]->price;
                        $mbprice = $mbprice + $books[0]->book_price;
                    } else {
                        $mprice = $mprice + $books[0]->half_price;
                        $mbprice = $mbprice + $books[0]->book_half_price;
                    }

                    $coin++;
                }
            }


            if ($bookq2->type == 1) {
                $fprice2 = $levelq2->price;
            } elseif ($bookq2->type == 2) {
                $fprice2 = $levelq2->half_price;
            }
        } else {
            $books = bookuse::select(
                "bookuses.*",
                "bookuses.type as level_type",
                "levels.name as level_name",
                "levels.price as price",
                "levels.half_price as half_price",
                "levels.book_price as book_price",
                "levels.book_half_price as book_half_price",
                "terms.name as term_name",
            )
                ->join("terms", "terms.id", "=", "bookuses.term_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('bookuses.level_id', '=', $request->get('level'))
                ->where('bookuses.term_id', '=', $request->get('term'))
                ->where('bookuses.id', '=', $request->get('bookuse'))
                ->get();
            $bookss[] = $books;
        }
        // dd($coin);

        $input = $request->all();
        $input['code'] = studentRunningNumber::generate($request->get('centre'));
        $input['level_name'] = $levelq->name;
        $input['term_name'] = $termq->name;
        $input['bookuse_name'] = $bookq->name;
        $input['type'] = 'student';

        if (!empty($request->get('level2'))) {
            $input['level2_name'] = $levelq2->name;
            $input['term2_name'] = $termq2->name;
            $input['bookuse2_name'] = $bookq2->name;
        }

        if ($request->get('ref')) {
            $ref_code = $request->get('ref');
            // $input['referal'] = $ref_code;
        } else {
            $ref_code = '';
            // $input['referal'] = '';
        }
        $courses_pending_id = $request->get('courses_pending_id');

        $input['courses_pending_id'] = $courses_pending_id;
        $contact = Contact::create($input);
        // dd($request->all(), $input);

        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $input_order['order_number'] = OrderRunningNumber::generate($request->get('centre'));
        $input_order['total_price'] = $mprice + $mbprice;
        $input_order['ref'] = $ref_code;
        $input_order['courses_pending_id'] = $courses_pending_id;
        $order = Order::create($input_order);

        foreach ($bookss as $dresult) {
            foreach ($dresult as $book) {
                if ($book->level_type == 1) {
                    $tprice = $book->price;
                    $bprice = $book->book_price;
                } elseif ($book->level_type == 2) {
                    $tprice = $book->half_price;
                    $bprice = $book->book_half_price;
                }
                OrderDetailList::create([
                    'order_id' => $order->id,
                    'level' => $book->level_name,
                    'term' => $book->term_name,
                    'book' => $book->name,
                    'price' => $tprice,
                    'bprice' => $bprice,
                ]);
            }
        }

        $contact->order = $order->id;
        $contact->save();

        $input_order_detail['order_id'] = $order->id;
        $input_order_detail['product_id'] = $bookq->id;
        $input_order_detail['pname'] = $bookq->name;
        $input_order_detail['punit'] = $bookq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $fprice;
        $order_detail = OrderDetail::create($input_order_detail);

        if (!empty($request->get('level2'))) {
            $input_order_detail2['order_id'] = $order->id;
            $input_order_detail2['product_id'] = $bookq2->id;
            $input_order_detail2['pname'] = $bookq2->name;
            $input_order_detail2['punit'] = $bookq2->unit;
            $input_order_detail2['quantity'] = 1;
            $input_order_detail2['price'] = $fprice2;
            $order_detail2 = OrderDetail::create($input_order_detail2);
        }

        $status = 'wait';
        $this->updateWaitingForPayment($status, $courses_pending_id);

        if ($request->get('ref')) {
            $commmissionList = create_commmission_list($order->id, $courses_pending_id, $request->get('ref')); // on helper
        }
        $select_list_contact = '<option value="' . $contact->id . '" > ' . $contact->name . '</option>';
        return response()->json([
            'success' => 'Added student information successfully.',
            'contact' => $select_list_contact,
            'cid' => $contact->id,
            'oid' => $order->id,
            'coin' => $coin,
        ]);
    }

    // private function create_commmission_list($courses_pending_id, $order_id)
    // {
    //     dd(
    //         $courses_pending_id,
    //         $order_id,
    //     );
    // }

    private function sendBroadcastMessage($accessToken, $message)
    {
        $accessToken = '2U8EhhLV69SeO/s/++xinSMV/g5r0K0WVvsaboSDAfGTX1Yz3820Xy/f7zSSz5wD+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMplrGoUWcLNE4NicviIUIcovVQ7Yub4BWefq7ABdgsr9QQdB04t89/1O/w1cDnyilFU='; // Replace with your Channel Access Token

        $client = new Client();
        $url = 'https://api.line.me/v2/bot/message/broadcast';

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            Log::error('Failed to send broadcast message to Line OA', ['response' => $response->getBody()->getContents()]);
        }
    }

    private function sendMulticastMessage($userIds, $accessToken, $message)
    {
        $accessToken = '2U8EhhLV69SeO/s/++xinSMV/g5r0K0WVvsaboSDAfGTX1Yz3820Xy/f7zSSz5wD+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMplrGoUWcLNE4NicviIUIcovVQ7Yub4BWefq7ABdgsr9QQdB04t89/1O/w1cDnyilFU='; // Replace with your Channel Access Token

        $client = new Client();
        $url = 'https://api.line.me/v2/bot/message/multicast';

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'to' => $userIds, // Array of Line user IDs
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            \Log::error('Failed to send multicast message to Line OA', ['response' => $response->getBody()->getContents()]);
        }
    }

    private function sendMessageToMyself($accessToken, $message)
    {

        $accessToken = '2U8EhhLV69SeO/s/++xinSMV/g5r0K0WVvsaboSDAfGTX1Yz3820Xy/f7zSSz5wD+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMplrGoUWcLNE4NicviIUIcovVQ7Yub4BWefq7ABdgsr9QQdB04t89/1O/w1cDnyilFU='; // Replace with your Line OA Channel Access Token
        $userId = 'U25fc5496e631e0cab0923f8f3cfc6db0'; // Replace with your Line User ID

        $client = new \GuzzleHttp\Client();
        $url = 'https://api.line.me/v2/bot/message/push';

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'to' => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            \Log::error('Failed to send message to myself', ['response' => $response->getBody()->getContents()]);
        }
    }

    /**
     * Get course pending details for view
     */
    public function getCoursePendingDetails($id)
    {
        $coursePending = CoursePending::with('department')->find($id);

        if (!$coursePending) {
            return response()->json([
                'success' => false,
                'message' => 'Course pending not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $coursePending
        ]);
    }
}
