<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\CourseLevel;
use Modules\Localization\Entities\Language;

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

        return view('affiliate.courses',compact(
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
}
