<div class="form-group">
    <label for="category_id">{{ __('content-management.faq.form.category') }}</label>
    <select
        class="form-control @error('category_id') is-invalid @enderror"
        id="category_id"
        name="category_id"
        required
    >
        <option value="">{{ __('content-management.faq.form.select_category') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ (string) old('category_id', optional($faq)->category_id) === (string) $category->id ? 'selected' : '' }}>
                {{ $category->name }}{{ $category->name_en ? ' / ' . $category->name_en : '' }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
        <label for="question">{{ __('content-management.faq.form.question_th') }}</label>
    <input
        type="text"
        class="form-control @error('question') is-invalid @enderror"
        id="question"
        name="question"
        value="{{ old('question', optional($faq)->question) }}"
        required
    >
    @error('question')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="question_en">{{ __('content-management.faq.form.question_en') }}</label>
    <input
        type="text"
        class="form-control @error('question_en') is-invalid @enderror"
        id="question_en"
        name="question_en"
        value="{{ old('question_en', optional($faq)->question_en) }}"
    >
    <small class="form-text text-muted">{{ __('content-management.faq.form.optional_thai_fallback') }}</small>
    @error('question_en')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <div class="d-flex justify-content-between">
        <label for="answer">{{ __('content-management.faq.form.answer_th') }}</label>
        <div id="upload-status-container-answer" class="text-sm"></div>
    </div>
    <textarea
        class="form-control @error('answer') is-invalid @enderror"
        id="answer"
        name="answer"
        rows="8"
    >{{ old('answer', optional($faq)->answer) }}</textarea>
    <small class="form-text text-muted">{{ __('content-management.faq.form.markdown_help') }}</small>
    @error('answer')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <div class="d-flex justify-content-between">
        <label for="answer_en">{{ __('content-management.faq.form.answer_en') }}</label>
        <div id="upload-status-container-answer_en" class="text-sm"></div>
    </div>
    <textarea
        class="form-control @error('answer_en') is-invalid @enderror"
        id="answer_en"
        name="answer_en"
        rows="8"
    >{{ old('answer_en', optional($faq)->answer_en) }}</textarea>
    <small class="form-text text-muted">{{ __('content-management.faq.form.optional_markdown_fallback') }}</small>
    @error('answer_en')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="position">{{ __('content-management.faq.form.position') }}</label>
    <input
        type="number"
        min="0"
        class="form-control @error('position') is-invalid @enderror"
        id="position"
        name="position"
        value="{{ old('position', optional($faq)->position ?? 0) }}"
    >
    @error('position')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="card card-outline card-secondary mb-3">
    <div class="card-header"><strong>{{ __('content-management.faq.form.editorial_information') }}</strong> <span class="text-muted">{{ __('content-management.faq.form.not_shown_on_website') }}</span></div>
    <div class="card-body">
        <div class="form-group">
            <label for="source_reference">{{ __('content-management.faq.form.source_reference') }}</label>
            <input type="number" min="0" class="form-control @error('source_reference') is-invalid @enderror" id="source_reference" name="source_reference" value="{{ old('source_reference', optional($faq)->source_reference) }}">
            @error('source_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="selection_reason">{{ __('content-management.faq.form.selection_reason') }}</label>
            <textarea class="form-control @error('selection_reason') is-invalid @enderror" id="selection_reason" name="selection_reason" rows="2">{{ old('selection_reason', optional($faq)->selection_reason) }}</textarea>
            @error('selection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group mb-0">
            <label for="publication_note">{{ __('content-management.faq.form.publication_note') }}</label>
            <textarea class="form-control @error('publication_note') is-invalid @enderror" id="publication_note" name="publication_note" rows="2">{{ old('publication_note', optional($faq)->publication_note) }}</textarea>
            @error('publication_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-check mb-3">
    <input type="hidden" name="is_published" value="0">
    <input
        type="checkbox"
        class="form-check-input @error('is_published') is-invalid @enderror"
        id="is_published"
        name="is_published"
        value="1"
        {{ old('is_published', optional($faq)->is_published) ? 'checked' : '' }}
    >
    <label class="form-check-label" for="is_published">{{ __('content-management.faq.form.publish_now') }}</label>
    @error('is_published')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<a href="{{ route('faqs.index') }}" class="btn btn-secondary">{{ __('content-management.faq.form.back') }}</a>
<button type="submit" class="btn btn-primary">{{ $submit_label }}</button>
