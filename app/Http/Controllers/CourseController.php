<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\CourseLevel;
use Modules\Localization\Entities\Language;
use App\Models\CoursePending;
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
        // dd($request);

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

    public function CoursePendingStore(Request $request)
    {
        // Validate the incoming request

        // $validated = $request->validate([
        //     'ref' => 'nullable|string|max:255',
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255',
        //     'telp' => 'nullable|string|max:15',
        //     'course_name' => 'nullable|string|max:255',
        //     // 'course_id' => 'required|exists:courses,id', // Ensure the course exists
        // ]);



        // try {
        //     // Store the data in the courses_pending table
        //     CoursePending::create([
        //         'ref' => $validated['ref'],
        //         'name' => $validated['name'],
        //         'email' => $validated['email'],
        //         'telp' => $validated['telp'],
        //         'course_name' => $validated['course_name'],
        //         'course_id' => $validated['course_id'],
        //     ]);

        //     // Flash a success message
        //     Toastr::success('Your course request has been submitted successfully.');

        //     // Redirect or close the modal
        //     return back();
        // } catch (\Exception $e) {
        //     // Flash an error message in case of failure
        //     Toastr::error('There was an error submitting your request.');

        //     // Optionally return the error
        //     return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        // }
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
}
