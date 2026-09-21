<?php

namespace App\Http\Controllers;

use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:faq-list', ['only' => ['Index']]);
        $this->middleware('permission:faq-create', ['only' => ['Create', 'Store']]);
        $this->middleware('permission:faq-edit', ['only' => ['Edit', 'Update']]);
        $this->middleware('permission:faq-delete', ['only' => ['Destroy']]);
    }

    public function Index()
    {
        $categories = FaqCategory::withCount('faqs')
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(20);

        return view('faq-categories.index', compact('categories'));
    }

    public function Create()
    {
        return view('faq-categories.create');
    }

    public function Store(Request $request)
    {
        $validated_category = $this->ValidateCategory($request);
        $validated_category['slug'] = $this->MakeUniqueSlug(
            $validated_category['name_en'] ?: $validated_category['name']
        );
        $validated_category['position'] = $validated_category['position'] ?? 0;

        FaqCategory::create($validated_category);

        return redirect()->route('faq-categories.index')->with('success', __('content-management.faq_categories.messages.created'));
    }

    public function Edit(FaqCategory $faq_category)
    {
        return view('faq-categories.edit', compact('faq_category'));
    }

    public function Update(Request $request, FaqCategory $faq_category)
    {
        $validated_category = $this->ValidateCategory($request);
        $validated_category['position'] = $validated_category['position'] ?? 0;

        $faq_category->update($validated_category);

        return redirect()->route('faq-categories.index')->with('success', __('content-management.faq_categories.messages.updated'));
    }

    public function Destroy(FaqCategory $faq_category)
    {
        if ($faq_category->faqs()->exists()) {
            return redirect()->route('faq-categories.index')->with(
                'error',
                __('content-management.faq_categories.messages.delete_blocked')
            );
        }

        $faq_category->delete();

        return redirect()->route('faq-categories.index')->with('success', __('content-management.faq_categories.messages.deleted'));
    }

    private function ValidateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['required', 'boolean'],
        ]);
    }

    private function MakeUniqueSlug(string $name): string
    {
        $base_slug = Str::slug($name) ?: 'faq-category';
        $slug = $base_slug;
        $suffix = 2;

        while (FaqCategory::where('slug', $slug)->exists()) {
            $slug = $base_slug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
