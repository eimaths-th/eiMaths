<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
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
        $faqs = Faq::orderBy('position')->orderByDesc('created_at')->paginate(20);

        return view('faqs.index', compact('faqs'));
    }

    public function Create()
    {
        return view('faqs.create');
    }

    public function Store(Request $request)
    {
        $validated_faq = $this->ValidateFaq($request);
        $validated_faq['position'] = $validated_faq['position'] ?? 0;
        $validated_faq['published_at'] = $validated_faq['is_published'] ? now() : null;

        Faq::create($validated_faq);

        return redirect()->route('faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function Edit(Faq $faq)
    {
        return view('faqs.edit', compact('faq'));
    }

    public function Update(Request $request, Faq $faq)
    {
        $validated_faq = $this->ValidateFaq($request);
        $validated_faq['position'] = $validated_faq['position'] ?? 0;
        $validated_faq['published_at'] = $validated_faq['is_published']
            ? ($faq->published_at ?? now())
            : null;

        $faq->update($validated_faq);

        return redirect()->route('faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function Destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    public function ApiIndex()
    {
        $faqs = Faq::where('is_published', true)
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get([
                'question',
                'answer',
                'position',
                'is_published',
                'published_at',
                'created_at',
                'updated_at',
            ])
            ->map(function (Faq $faq) {
                return [
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'answer_html' => Str::markdown($faq->answer),
                    'position' => $faq->position,
                    'is_published' => $faq->is_published,
                    'published_at' => $faq->published_at,
                    'created_at' => $faq->created_at,
                    'updated_at' => $faq->updated_at,
                ];
            });

        return response()->json($faqs);
    }

    private function ValidateFaq(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['required', 'boolean'],
        ]);
    }
}
