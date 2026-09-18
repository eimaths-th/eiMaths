<div class="modal fade" id="fast-history-modal" tabindex="-1" role="dialog" aria-labelledby="fast-history-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="fast-history-modal-title">
                    <i class="fa fa-user-check"></i> {{ __('students.check_in.confirm_title') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="fast-history-student" class="font-weight-bold mb-3"></p>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fast-history-level">{{ __('common.fields.level') }}</label>
                        <select id="fast-history-level" class="form-control"></select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fast-history-term">{{ __('common.fields.term') }}</label>
                        <select id="fast-history-term" class="form-control"></select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fast-history-book">{{ __('common.fields.book') }}</label>
                        <select id="fast-history-book" class="form-control"></select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fast-history-teacher">{{ __('common.fields.teacher') }}</label>
                        <select id="fast-history-teacher" class="form-control">
                            <option value="0">{{ __('students.check_in.no_teacher') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="fast-history-date">{{ __('students.check_in.study_date') }}</label>
                        <input type="text" id="fast-history-date" class="form-control fast-history-date"
                            autocomplete="off">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fast-history-stime">{{ __('students.check_in.start_time') }}</label>
                        <input type="text" id="fast-history-stime" class="form-control fast-history-time"
                            autocomplete="off">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fast-history-etime">{{ __('students.check_in.end_time') }}</label>
                        <input type="text" id="fast-history-etime" class="form-control fast-history-time"
                            autocomplete="off">
                    </div>
                </div>
                <div class="alert alert-light border mb-0">
                    {{ __('students.check_in.remaining_after') }}:
                    <strong id="fast-history-remaining"></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ __('common.actions.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirm-fast-history">
                    <i class="fa fa-check"></i> {{ __('common.actions.confirm') }}
                </button>
            </div>
        </div>
    </div>
</div>
