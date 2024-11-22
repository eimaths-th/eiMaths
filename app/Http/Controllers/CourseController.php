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
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request)
    {
        // Validate the incoming request

        $validated = $request->validate([
            'ref' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telp' => 'nullable|string|max:15',
            'course_name' => 'nullable|string|max:255',
            // 'course_id' => 'required|exists:courses,id', // Ensure the course exists
        ]);
        dd($request);

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

        Toastr::success(trans('common.Operation successful'), trans('common.Success'));
        return redirect()->route('categories.index');
    }
    public function categoriesDelete($id)
    {
        $category = Category::find($id);

        if ($category) {
            $category->delete(); // Deletes the category
            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
    }
    public function courselList() {}
    public function courseLevel() {}

    public function category_status_update(Request $request)
    {
        // dd($request);
        if (demoCheck()) {
            return redirect()->back();
        }
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
}
