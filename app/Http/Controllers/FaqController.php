<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:faq-list', ['only' => ['Index']]);
        $this->middleware('permission:faq-create', ['only' => ['Create', 'Store']]);
        $this->middleware('permission:faq-edit', ['only' => ['Edit', 'Update']]);
        $this->middleware('permission:faq-delete', ['only' => ['Destroy']]);
    }

    public function Index(Request $request)
    {
        $selected_category_id = $request->integer('category_id');
        $categories = FaqCategory::orderBy('position')->orderBy('name')->get();
        $faqs = Faq::with('category')
            ->when($selected_category_id, function ($query) use ($selected_category_id) {
                $query->where('category_id', $selected_category_id);
            })
            ->orderBy(
                FaqCategory::select('position')
                    ->whereColumn('faq_categories.id', 'faqs.category_id')
            )
            ->orderBy('position')
            ->orderByDesc('faqs.created_at')
            ->paginate(20)
            ->withQueryString();

        return view('faqs.index', compact('faqs', 'categories', 'selected_category_id'));
    }

    public function Create()
    {
        $categories = FaqCategory::orderBy('position')->orderBy('name')->get();

        return view('faqs.create', compact('categories'));
    }

    public function Store(Request $request)
    {
        $validated_faq = $this->ValidateFaq($request);
        $validated_faq['position'] = $validated_faq['position'] ?? 0;
        $validated_faq['published_at'] = $validated_faq['is_published'] ? now() : null;

        app(\App\Services\Faq\FaqSlugWriter::class)->Create($validated_faq);

        return redirect()->route('faqs.index')->with('success', __('content-management.faq.messages.created'));
    }

    public function Edit(Faq $faq)
    {
        $categories = FaqCategory::orderBy('position')->orderBy('name')->get();

        return view('faqs.edit', compact('faq', 'categories'));
    }

    public function Update(Request $request, Faq $faq)
    {
        $validated_faq = $this->ValidateFaq($request, $faq);
        $validated_faq['position'] = $validated_faq['position'] ?? 0;
        $validated_faq['published_at'] = $validated_faq['is_published']
            ? ($faq->published_at ?? now())
            : null;

        $faq->update($validated_faq);

        return redirect()->route('faqs.index')->with('success', __('content-management.faq.messages.updated'));
    }

    public function Destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('faqs.index')->with('success', __('content-management.faq.messages.deleted'));
    }

    public function ApiIndex(Request $request)
    {
        $locale = in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
        $content = app(\App\Services\Faq\FaqPublicContent::class);
        return response()->json($content->Query()->get()->map(
            fn (Faq $faq): array => $content->Serialize($faq, $locale)
        ));
    }
    public function ApiShow(Request $request, string $slug)
    {
        $locale = in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
        $content = app(\App\Services\Faq\FaqPublicContent::class);
        $faq = $content->Query()->where('faqs.slug', $slug)->firstOrFail();
        return response()->json($content->Serialize($faq, $locale));
    }

    private function ValidateFaq(Request $request, ?Faq $faq = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:faq_categories,id'],
            'question' => ['required', 'string', 'max:255'],
            'question_en' => ['nullable', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'answer_en' => ['nullable', 'string'],
            'source_reference' => [
                'nullable',
                'integer',
                'min:0',
                Rule::unique('faqs', 'source_reference')->ignore($faq?->id),
            ],
            'selection_reason' => ['nullable', 'string'],
            'publication_note' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['required', 'boolean'],
        ]);
    }
}
