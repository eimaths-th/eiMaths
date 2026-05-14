<div class="form-group">
    <label for="question">Question</label>
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
    <div class="d-flex justify-content-between">
        <label for="answer">Answer</label>
        <div id="upload-status-container" class="text-sm"></div>
    </div>
    <textarea
        class="form-control @error('answer') is-invalid @enderror"
        id="answer"
        name="answer"
        rows="8"
    >{{ old('answer', optional($faq)->answer) }}</textarea>
    <small class="form-text text-muted">Markdown content. Formatting, links, and uploaded images will be shown on the website.</small>
    @error('answer')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="position">Position</label>
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
    <label class="form-check-label" for="is_published">Publish Now</label>
    @error('is_published')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<a href="{{ route('faqs.index') }}" class="btn btn-secondary">Back</a>
<button type="submit" class="btn btn-primary">{{ $submit_label }}</button>
