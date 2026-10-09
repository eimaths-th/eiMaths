<div class="card" style="max-width:1000px">
    <div class="card-header">{{ __('ebooks.import.title') }}</div>
    <div class="card-body">
        <form action="{{ route($routePrefix.'.import') }}" method="post" enctype="multipart/form-data" id="ebook-import-form">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group"><label for="import-level">{{ __('common.fields.level') }}</label><select id="import-level" class="form-control" required><option value="">{{ __('ebooks.import.choose_level') }}</option></select></div>
                <div class="col-md-4 form-group"><label for="import-term">{{ __('common.fields.term') }}</label><select id="import-term" class="form-control" required disabled><option value="">{{ __('ebooks.import.choose_term') }}</option></select></div>
                <div class="col-md-4 form-group"><label for="catalog-key">{{ __('ebooks.fields.book_code') }}</label><select id="catalog-key" class="form-control" name="catalog_key" required disabled><option value="">{{ __('ebooks.import.choose_book') }}</option></select></div>
            </div>
            <div id="catalog-info" class="alert alert-light border mb-4" aria-live="polite">{{ __('ebooks.import.choose_book_help') }}</div>
            <div class="eb-upload mb-4">
                <label for="ebook-pdf" class="d-block mb-3"><i class="far fa-file-pdf text-danger mr-2"></i>{{ __('ebooks.import.pdf_file') }}</label>
                <div class="custom-file">
                    <input type="file" id="ebook-pdf" name="pdf" accept=".pdf,application/pdf" required class="custom-file-input" aria-describedby="ebook-file-limits">
                    <label class="custom-file-label text-truncate" for="ebook-pdf" id="ebook-file-name" data-browse="{{ __('ebooks.import.choose_file') }}">{{ __('ebooks.import.no_file') }}</label>
                </div>
                <div id="ebook-file-limits" class="eb-help mt-3">{{ __('ebooks.import.limits', ['pages' => config('ebook.max_pages')]) }}</div>
            </div>
            <div class="eb-form-actions"><button type="submit" class="btn btn-primary px-4" id="import-submit"><i class="fas fa-upload mr-1"></i>{{ __('ebooks.actions.import_pdf') }}</button><a class="btn btn-light" href="{{ route($routePrefix.'.index') }}">{{ __('ebooks.actions.back_library') }}</a></div>
        </form>
    </div>
</div>
<script>
(() => {
    const volumes = @json($volumes->values());
    const existing = @json($books->pluck('code'));
    const selectedKey = @json(old('catalog_key', request('catalog_key', '')));
    const texts = @json(__('ebooks.import'));
    const level = document.getElementById('import-level'), term = document.getElementById('import-term'), book = document.getElementById('catalog-key');
    const info = document.getElementById('catalog-info');
    const file = document.getElementById('ebook-pdf');
    function fill(select, rows, label, valueKey, textKey) {
        select.replaceChildren(new Option(label, ''));
        const seen = new Set();
        rows.forEach(v => { if (!seen.has(v[valueKey])) { select.add(new Option(v[textKey], v[valueKey])); seen.add(v[valueKey]); } });
    }
    function updateInfo() {
        const v = volumes.find(v => v.key === book.value);
        if (!v) { info.textContent = texts.choose_book_help; return; }
        const values = {code: v.code, level: v.level, term: v.term, sets: v.sets.join(', ')};
        info.textContent = texts.selection.replace(/:(code|level|term|sets)\b/g, (_, key) => values[key])
            + ' — ' + (existing.includes(v.code) ? texts.existing_edition : texts.existing_registration);
    }
    function updateBooks() { fill(book, volumes.filter(v => String(v.level_id) === level.value && String(v.term_id) === term.value), texts.choose_book, 'key', 'code'); book.disabled = !term.value; updateInfo(); }
    function updateTerms() { fill(term, volumes.filter(v => String(v.level_id) === level.value), texts.choose_term, 'term_id', 'term'); term.disabled = !level.value; updateBooks(); }
    fill(level, volumes, texts.choose_level, 'level_id', 'level');
    level.addEventListener('change', updateTerms); term.addEventListener('change', updateBooks); book.addEventListener('change', updateInfo);
    file.addEventListener('change', () => { document.getElementById('ebook-file-name').textContent = file.files[0]?.name ?? texts.no_file; });
    const selected = volumes.find(v => v.key === selectedKey);
    if (selected) { level.value = selected.level_id; updateTerms(); term.value = selected.term_id; updateBooks(); book.value = selected.key; updateInfo(); }
    document.getElementById('ebook-import-form').addEventListener('submit', event => {
        if (file.files[0]?.size > 20 * 1024 * 1024) { event.preventDefault(); info.textContent = texts.too_large; return; }
        const button = document.getElementById('import-submit'); button.disabled = true; button.textContent = texts.submitting;
    });
})();
</script>
