<div class="form-group">
    <label for="name">{{ __('content-management.faq_categories.form.name_th') }}</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', optional($faq_category)->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="name_en">{{ __('content-management.faq_categories.form.name_en') }}</label>
    <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en" name="name_en" value="{{ old('name_en', optional($faq_category)->name_en) }}">
    <small class="form-text text-muted">{{ __('content-management.faq_categories.form.optional_thai_fallback') }}</small>
    @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="description">{{ __('content-management.faq_categories.form.description_th') }}</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', optional($faq_category)->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="description_en">{{ __('content-management.faq_categories.form.description_en') }}</label>
    <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en" rows="3">{{ old('description_en', optional($faq_category)->description_en) }}</textarea>
    @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="position">{{ __('content-management.faq_categories.form.position') }}</label>
    <input type="number" min="0" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', optional($faq_category)->position ?? 0) }}">
    @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="is_published" value="0">
    <input type="checkbox" class="form-check-input @error('is_published') is-invalid @enderror" id="is_published" name="is_published" value="1" {{ old('is_published', optional($faq_category)->is_published ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_published">{{ __('content-management.faq_categories.form.publish_category') }}</label>
    @error('is_published')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<a href="{{ route('faq-categories.index') }}" class="btn btn-secondary">{{ __('content-management.faq_categories.form.back') }}</a>
<button type="submit" class="btn btn-primary">{{ $submit_label }}</button>
