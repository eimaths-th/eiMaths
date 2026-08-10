@php
    $user_messages = [
        'another_action' => trans('book-shipments.hq.another_action', [], 'th'),
        'action_failed' => trans('book-shipments.hq.action_failed', [], 'th'),
        'selection_editor_load_error' => trans('book-shipments.hq.selection_editor_load_error', [], 'th'),
        'selected_books_empty' => trans('book-shipments.hq.selected_books_empty', [], 'th'),
        'student_fallback' => trans('book-shipments.hq.student_fallback', [], 'th'),
        'unnamed_student' => trans('book-shipments.hq.unnamed_student', [], 'th'),
        'unnamed_book' => trans('book-shipments.hq.unnamed_book', [], 'th'),
        'no_level' => trans('book-shipments.hq.no_level', [], 'th'),
        'term' => trans('book-shipments.hq.term', [], 'th'),
        'no_order_number' => trans('book-shipments.hq.no_order_number', [], 'th'),
        'remove' => trans('book-shipments.hq.remove', [], 'th'),
        'show_books' => trans('book-shipments.hq.show_books', [], 'th'),
        'hide_books' => trans('book-shipments.hq.hide_books', [], 'th'),
        'no_eligible_books' => trans('book-shipments.hq.no_eligible_books', [], 'th'),
        'select_all_books_for' => trans('book-shipments.hq.select_all_books_for', [], 'th'),
        'select_book_for' => trans('book-shipments.hq.select_book_for', [], 'th'),
        'replacement_for' => trans('book-shipments.hq.replacement_for', [], 'th'),
        'page_summary' => trans('book-shipments.hq.page_summary', [], 'th'),
        'previous' => trans('book-shipments.hq.previous', [], 'th'),
        'next' => trans('book-shipments.hq.next', [], 'th'),
        'choose_centre_begin' => trans('book-shipments.hq.choose_centre_begin', [], 'th'),
        'loading' => 'กำลังโหลดหนังสือนักเรียนใหม่ที่จัดส่งได้…',
        'student_count_loaded' => trans('book-shipments.hq.student_count_loaded', [], 'th'),
        'no_candidates_loaded' => trans('book-shipments.hq.no_candidates_loaded', [], 'th'),
        'select_at_least_one' => trans('book-shipments.hq.select_at_least_one', [], 'th'),
        'saving' => trans('book-shipments.hq.saving', [], 'th'),
        'save_draft_changes' => trans('book-shipments.hq.save_draft_changes', [], 'th'),
        'save_draft' => trans('book-shipments.hq.save_draft', [], 'th'),
        'reason_validation' => trans('book-shipments.hq.reason_validation', [], 'th'),
        'confirm_dispatch' => trans('book-shipments.hq.confirm_dispatch', [], 'th'),
        'confirm_discard' => trans('book-shipments.hq.confirm_discard', [], 'th'),
        'confirm_cancel' => trans('book-shipments.hq.confirm_cancel', [], 'th'),
        'dispatching' => trans('book-shipments.hq.dispatching', [], 'th'),
        'discarding' => trans('book-shipments.hq.discarding', [], 'th'),
        'cancelling' => trans('book-shipments.hq.cancelling', [], 'th'),
    ];
@endphp

<script>
    (async () => {
        const csrf_token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const editor = document.getElementById('book-shipment-editor');
        const detail_status = document.getElementById('book-shipment-detail-status');
        const user_messages = @json($user_messages);

        const format_message = (template, replacements = {}) => Object.entries(replacements)
            .reduce((message, [placeholder, value]) => message.replaceAll(`:${placeholder}`, String(value)), template);

        const set_text = (element, value) => {
            if (element) {
                element.textContent = value || '';
            }
        };

        const read_error_message = (payload, fallback) => {
            if (payload?.error?.message) {
                return payload.error.message;
            }

            const details = payload?.error?.details;
            if (details && typeof details === 'object') {
                const first_detail = Object.values(details).flat()?.[0];
                if (first_detail) {
                    return first_detail;
                }
            }

            return fallback;
        };

        const request_json = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf_token,
                    ...(options.headers || {}),
                },
            });
            const payload = await response.json().catch(() => ({}));

            if (response.status === 409) {
                set_text(detail_status || document.getElementById('book-shipment-editor-status'),
                    user_messages.another_action);
                window.location.reload();
                return null;
            }

            if (!response.ok) {
                const error = new Error(read_error_message(payload, user_messages.action_failed));
                error.payload = payload;
                error.status = response.status;
                throw error;
            }

            return payload;
        };

        if (editor) {
            const destination_select = document.getElementById('book-shipment-destination');
            const search_input = document.getElementById('book-shipment-candidate-search');
            const load_button = document.getElementById('book-shipment-load-candidates');
            const candidate_list = document.getElementById('book-shipment-candidate-list');
            const editor_status = document.getElementById('book-shipment-editor-status');
            const selected_count = document.getElementById('book-shipment-selected-count');
            const selected_list = document.getElementById('book-shipment-selected-list');
            const selected_guidance = document.getElementById('book-shipment-selected-guidance');
            const save_button = document.getElementById('book-shipment-save-draft');
            let selection_factory;
            try {
                const selection_module = await import(editor.dataset.selectionModuleUrl);
                selection_factory = selection_module.CreateBookShipmentDraftSelection;
            } catch (error) {
                set_text(editor_status, user_messages.selection_editor_load_error);
                return;
            }

            const initial_selected_books = Array.from(editor.querySelectorAll('[data-selected-book-id]')).map((row) => ({
                source_book_id: row.dataset.selectedBookId,
                student_code: row.dataset.studentCode,
                student_name: row.dataset.studentName,
                book_name: row.dataset.bookName,
                level: row.dataset.level,
                term: row.dataset.term,
                order_number: row.dataset.orderNumber,
            }));
            const selected_books = selection_factory(initial_selected_books);
            let current_page = 1;
            let search_timer = null;
            let is_loading = false;
            let is_saving = false;
            let visible_students = [];
            let visible_meta = null;

            const update_selection_summary = () => {
                const selection_count = selected_books.Count();
                set_text(selected_count, String(selection_count));
                save_button.disabled = is_saving || selection_count === 0;
            };

            const set_student_checkbox_state = (student_checkbox, books) => {
                const book_ids = books.map((book) => Number(book.source_book_id));
                const selected_book_count = book_ids.filter((book_id) => selected_books.HasBook(book_id)).length;
                student_checkbox.checked = book_ids.length > 0 && selected_book_count === book_ids.length;
                student_checkbox.indeterminate = selected_book_count > 0 && selected_book_count < book_ids.length;
            };

            const render_selected_books = () => {
                if (!selected_list) {
                    return;
                }

                selected_list.replaceChildren();
                const books = selected_books.Books();
                if (!books.length) {
                    const empty_message = document.createElement('p');
                    empty_message.className = 'book-shipment-selected-empty text-muted small mb-0';
                    empty_message.textContent = user_messages.selected_books_empty;
                    selected_list.appendChild(empty_message);
                    if (selected_guidance) {
                        selected_guidance.classList.remove('d-none');
                    }
                    return;
                }

                if (selected_guidance) {
                    selected_guidance.classList.add('d-none');
                }

                books.forEach((book) => {
                    const selected_row = document.createElement('div');
                    selected_row.className = 'book-shipment-selected-row';
                    selected_row.dataset.selectedBookId = String(book.source_book_id);

                    const selected_details = document.createElement('div');
                    const student_label = document.createElement('strong');
                    student_label.textContent = `${book.student_code || user_messages.student_fallback} — ${book.student_name || user_messages.unnamed_student}`;
                    const book_meta = document.createElement('span');
                    book_meta.className = 'd-block small text-muted';
                    book_meta.textContent = `${book.book_name || user_messages.unnamed_book} · ${book.level || user_messages.no_level} · ${user_messages.term} ${book.term || '—'} · ${book.order_number || user_messages.no_order_number}`;
                    selected_details.append(student_label, book_meta);

                    const remove_button = document.createElement('button');
                    remove_button.type = 'button';
                    remove_button.className = 'btn btn-sm btn-outline-danger';
                    remove_button.dataset.removeSelectedBook = 'true';
                    remove_button.dataset.bookId = String(book.source_book_id);
                    remove_button.textContent = user_messages.remove;

                    selected_row.append(selected_details, remove_button);
                    selected_list.appendChild(selected_row);
                });
            };

            const render_candidate_page = (students, meta) => {
                visible_students = students;
                visible_meta = meta;
                candidate_list.replaceChildren();

                if (!students.length) {
                    const empty_message = document.createElement('p');
                    empty_message.className = 'text-muted small p-4 mb-0';
                    empty_message.textContent = user_messages.no_eligible_books;
                    candidate_list.appendChild(empty_message);
                    return;
                }

                students.forEach((student) => {
                    const student_card = document.createElement('div');
                    student_card.className = 'border-bottom p-3';

                    const student_header = document.createElement('div');
                    student_header.className = 'd-flex flex-wrap align-items-center';

                    const student_checkbox = document.createElement('input');
                    student_checkbox.type = 'checkbox';
                    student_checkbox.className = 'mr-2';
                    student_checkbox.id = `student-${student.student_id}`;
                    student_checkbox.dataset.studentSelect = 'true';
                    student_checkbox.dataset.studentId = String(student.student_id);
                    student_checkbox.setAttribute('aria-label', format_message(user_messages.select_all_books_for, {
                        student: student.student_name || student.student_code || user_messages.student_fallback,
                    }));
                    set_student_checkbox_state(student_checkbox, student.books || []);

                    const student_label = document.createElement('label');
                    student_label.className = 'mb-0 mr-3';
                    student_label.setAttribute('for', student_checkbox.id);
                    student_label.textContent = `${student.student_code || user_messages.student_fallback} — ${student.student_name || user_messages.unnamed_student}`;

                    const expand_button = document.createElement('button');
                    expand_button.type = 'button';
                    expand_button.className = 'btn btn-sm btn-link ml-auto';
                    expand_button.textContent = user_messages.show_books;
                    expand_button.setAttribute('aria-expanded', 'false');
                    expand_button.dataset.expandBooks = 'true';
                    const books_id = `student-books-${student.student_id}`;
                    expand_button.setAttribute('aria-controls', books_id);

                    student_header.append(student_checkbox, student_label, expand_button);
                    student_card.appendChild(student_header);

                    const books_panel = document.createElement('div');
                    books_panel.id = books_id;
                    books_panel.className = 'ml-4 mt-3';
                    books_panel.hidden = true;

                    (student.books || []).forEach((book) => {
                        const book_row = document.createElement('label');
                        book_row.className = 'd-flex align-items-start border rounded p-2 mb-2';

                        const book_checkbox = document.createElement('input');
                        book_checkbox.type = 'checkbox';
                        book_checkbox.className = 'mr-2 mt-1';
                        book_checkbox.checked = selected_books.HasBook(Number(book.source_book_id));
                        book_checkbox.dataset.bookSelect = 'true';
                        book_checkbox.dataset.bookId = String(book.source_book_id);
                        book_checkbox.dataset.studentId = String(student.student_id);
                        book_checkbox.setAttribute('aria-label', format_message(user_messages.select_book_for, {
                            book: book.book_name || user_messages.unnamed_book,
                            student: student.student_name || student.student_code || user_messages.student_fallback,
                        }));

                        const book_text = document.createElement('span');
                        const book_title = document.createElement('strong');
                        book_title.textContent = book.book_name || user_messages.unnamed_book;
                        const book_meta = document.createElement('span');
                        book_meta.className = 'd-block small text-muted';
                        book_meta.textContent = `${book.level || user_messages.no_level} · ${user_messages.term} ${book.term || '—'} · ${book.order_number || user_messages.no_order_number}`;
                        if (book.replacement_for_shipment_item_id) {
                            const replacement_context = document.createElement('span');
                            replacement_context.className = 'd-block small text-warning';
                            replacement_context.textContent = format_message(user_messages.replacement_for, { id: book.replacement_for_shipment_item_id });
                            book_meta.appendChild(replacement_context);
                        }
                        book_text.append(book_title, book_meta);
                        book_row.append(book_checkbox, book_text);
                        books_panel.appendChild(book_row);
                    });

                    student_card.appendChild(books_panel);
                    candidate_list.appendChild(student_card);
                });

                const pagination = document.createElement('div');
                pagination.className = 'd-flex justify-content-between align-items-center p-3';
                const page_label = document.createElement('span');
                page_label.className = 'small text-muted';
                page_label.textContent = format_message(user_messages.page_summary, {
                    current: meta.current_page,
                    last: meta.last_page,
                    total: meta.total,
                });
                const page_controls = document.createElement('div');
                const previous_button = document.createElement('button');
                previous_button.type = 'button';
                previous_button.className = 'btn btn-sm btn-outline-secondary mr-2';
                previous_button.textContent = user_messages.previous;
                previous_button.disabled = meta.current_page <= 1;
                previous_button.dataset.candidatePage = String(meta.current_page - 1);
                const next_button = document.createElement('button');
                next_button.type = 'button';
                next_button.className = 'btn btn-sm btn-outline-secondary';
                next_button.textContent = user_messages.next;
                next_button.disabled = meta.current_page >= meta.last_page;
                next_button.dataset.candidatePage = String(meta.current_page + 1);
                page_controls.append(previous_button, next_button);
                pagination.append(page_label, page_controls);
                candidate_list.appendChild(pagination);
            };

            candidate_list.addEventListener('change', (event) => {
                const student_checkbox = event.target.closest('[data-student-select]');
                const book_checkbox = event.target.closest('[data-book-select]');

                if (student_checkbox) {
                    const student = visible_students.find((candidate) => Number(candidate.student_id) === Number(student_checkbox.dataset.studentId));
                    (student?.books || []).forEach((book) => {
                        if (student_checkbox.checked) {
                            selected_books.AddBook({
                                ...book,
                                student_code: student.student_code,
                                student_name: student.student_name,
                            });
                        } else {
                            selected_books.RemoveBook(Number(book.source_book_id));
                        }
                    });
                    render_candidate_page(visible_students, visible_meta);
                    render_selected_books();
                    update_selection_summary();
                    return;
                }

                if (book_checkbox) {
                    const book_id = Number(book_checkbox.dataset.bookId);
                    const student = visible_students.find((candidate) => Number(candidate.student_id) === Number(book_checkbox.dataset.studentId));
                    if (book_checkbox.checked) {
                        const book = (student?.books || []).find((candidate) => Number(candidate.source_book_id) === book_id);
                        if (book) {
                            selected_books.AddBook({
                                ...book,
                                student_code: student.student_code,
                                student_name: student.student_name,
                            });
                        }
                    } else {
                        selected_books.RemoveBook(book_id);
                    }
                    const current_student_checkbox = Array.from(candidate_list.querySelectorAll('[data-student-select]'))
                        .find((checkbox) => Number(checkbox.dataset.studentId) === Number(book_checkbox.dataset.studentId));
                    if (current_student_checkbox) {
                        set_student_checkbox_state(current_student_checkbox, student?.books || []);
                    }
                    render_selected_books();
                    update_selection_summary();
                }
            });

            selected_list?.addEventListener('click', (event) => {
                const remove_button = event.target.closest('[data-remove-selected-book]');
                if (!remove_button) {
                    return;
                }

                selected_books.RemoveBook(Number(remove_button.dataset.bookId));
                render_candidate_page(visible_students, visible_meta);
                render_selected_books();
                update_selection_summary();
            });

            candidate_list.addEventListener('click', (event) => {
                const expand_button = event.target.closest('[data-expand-books]');
                if (expand_button) {
                    const is_expanded = expand_button.getAttribute('aria-expanded') === 'true';
                    expand_button.setAttribute('aria-expanded', String(!is_expanded));
                    expand_button.textContent = is_expanded ? user_messages.show_books : user_messages.hide_books;
                    const books_panel = document.getElementById(expand_button.getAttribute('aria-controls'));
                    if (books_panel) {
                        books_panel.hidden = is_expanded;
                    }
                    return;
                }

                const page_button = event.target.closest('[data-candidate-page]');
                if (page_button && !page_button.disabled) {
                    load_candidates(Number(page_button.dataset.candidatePage));
                }
            });

            const load_candidates = async (page = 1) => {
                const destination_id = destination_select?.value;
                if (!destination_id) {
                    set_text(editor_status, user_messages.choose_centre_begin);
                    return;
                }

                if (is_loading) {
                    return;
                }

                is_loading = true;
                load_button.disabled = true;
                set_text(editor_status, user_messages.loading);
                const query = new URLSearchParams({
                    destination_centre_id: destination_id,
                    search: search_input?.value || '',
                    page: String(page),
                    per_page: '20',
                });
                if (editor.dataset.mode === 'update' && editor.dataset.draftShipmentId) {
                    query.set('draft_shipment_id', editor.dataset.draftShipmentId);
                }

                try {
                    const payload = await request_json(`${editor.dataset.candidatesUrl}?${query.toString()}`);
                    if (!payload) {
                        return;
                    }
                    current_page = payload.meta.current_page;
                    render_candidate_page(payload.data || [], payload.meta);
                    set_text(editor_status, format_message(user_messages.student_count_loaded, { count: payload.meta.total }));
                    update_selection_summary();
                } catch (error) {
                    set_text(editor_status, error.message);
                } finally {
                    is_loading = false;
                    load_button.disabled = !destination_select?.value;
                }
            };

            destination_select?.addEventListener('change', () => {
                if (editor.dataset.mode !== 'update') {
                    selected_books.Books().forEach((book) => selected_books.RemoveBook(book.source_book_id));
                    render_selected_books();
                }
                update_selection_summary();
                if (destination_select.value) {
                    load_candidates(1);
                } else {
                    candidate_list.replaceChildren();
                    const empty_message = document.createElement('p');
                    empty_message.className = 'text-muted small p-4 mb-0';
                    empty_message.textContent = user_messages.no_candidates_loaded;
                    candidate_list.appendChild(empty_message);
                    set_text(editor_status, user_messages.choose_centre_begin);
                }
            });

            search_input?.addEventListener('input', () => {
                window.clearTimeout(search_timer);
                search_timer = window.setTimeout(() => load_candidates(1), 300);
            });

            load_button?.addEventListener('click', () => load_candidates(current_page));

            save_button?.addEventListener('click', async () => {
                if (selected_books.Count() === 0 || is_saving) {
                    set_text(editor_status, user_messages.select_at_least_one);
                    return;
                }

                is_saving = true;
                update_selection_summary();
                save_button.textContent = user_messages.saving;
                const source_book_ids = selected_books.SourceBookIds();
                const is_update = editor.dataset.mode === 'update';
                const request_body = { source_book_ids };
                if (!is_update) {
                    request_body.destination_centre_id = Number(destination_select.value);
                }

                try {
                    const payload = await request_json(is_update ? editor.dataset.updateUrl : editor.dataset.storeUrl, {
                        method: is_update ? 'PUT' : 'POST',
                        body: JSON.stringify(request_body),
                    });
                    if (!payload) {
                        return;
                    }
                    const shipment_id = payload.data?.shipment?.id;
                    if (is_update) {
                        window.location.reload();
                    } else if (shipment_id) {
                        window.location.assign(`/book-shipments/${shipment_id}`);
                    }
                } catch (error) {
                    set_text(editor_status, error.message);
                } finally {
                    is_saving = false;
                    save_button.textContent = editor.dataset.mode === 'update' ? user_messages.save_draft_changes : user_messages.save_draft;
                    update_selection_summary();
                }
            });

            render_selected_books();
            update_selection_summary();
            if (destination_select?.value) {
                load_candidates(1);
            }
        }

        document.querySelectorAll('[data-transition-form]').forEach((transition_form) => {
            transition_form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const transition = transition_form.dataset.transition;
                const submit_button = transition_form.querySelector('button[type="submit"]');
                const transition_reason = transition_form.querySelector('[name="reason"]');
                const transition_error = transition_form.querySelector('.invalid-feedback');
                const requires_reason = transition === 'cancel' || transition === 'discard';

                if (requires_reason) {
                    const reason = transition_reason?.value.trim() || '';
                    set_text(transition_error, '');
                    if (reason.length < 3 || reason.length > 1000) {
                        const reason_label = transition === 'discard' ? 'ยกเลิกแบบร่าง' : 'ยกเลิกการจัดส่ง';
                        set_text(transition_error, format_message(user_messages.reason_validation, { type: reason_label }));
                        transition_reason?.focus();
                        return;
                    }
                }

                const confirmation_message = transition === 'dispatch'
                    ? user_messages.confirm_dispatch
                    : transition === 'discard'
                        ? user_messages.confirm_discard
                        : user_messages.confirm_cancel;
                if (!window.confirm(confirmation_message)) {
                    return;
                }

                submit_button.disabled = true;
                const status_message = transition === 'dispatch'
                    ? user_messages.dispatching
                    : transition === 'discard'
                        ? user_messages.discarding
                        : user_messages.cancelling;
                set_text(detail_status, status_message);
                const request_body = requires_reason
                    ? { reason: transition_reason.value.trim() }
                    : {};

                try {
                    const payload = await request_json(transition_form.dataset.actionUrl, {
                        method: 'POST',
                        body: JSON.stringify(request_body),
                    });
                    if (payload) {
                        if (transition === 'discard' && transition_form.dataset.successUrl) {
                            window.location.assign(transition_form.dataset.successUrl);
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (error) {
                    set_text(transition_error, error.message);
                    set_text(detail_status, error.message);
                    submit_button.disabled = false;
                }
            });
        });
    })();
</script>
