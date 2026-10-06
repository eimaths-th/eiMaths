import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

// Execute the teacher-selection portion of the registered edit response handler.
const script = fs.readFileSync(new URL('../../resources/views/histories/script.blade.php', import.meta.url), 'utf8');
const edit_start = script.indexOf("route('histories.edit', ':id')");
const handler_start = script.indexOf('success: function(res) {', edit_start);
const handler_body = script.slice(handler_start + 'success: function(res) {'.length, script.indexOf("$('#getCentre')", handler_start));

class TeacherOption {
    constructor(text, value) {
        this.text = text;
        this.value = String(value);
        this.dataset = {};
    }

    remove() {
        this.owner.options = this.owner.options.filter(option => option !== this);
    }
}

function TeacherSelect() {
    const teacher_select = {
        options: [], selected_value: '',
        add(option) { option.owner = this; this.options.push(option); },
        get value() { return this.selected_value; },
        set value(selected_value) {
            this.selected_value = this.options.some(option => option.value === String(selected_value)) ? String(selected_value) : '';
        },
    };
    teacher_select.add(new TeacherOption('Choose teacher', ''));
    teacher_select.add(new TeacherOption('Current branch teacher', '1'));
    return teacher_select;
}

function ApplyEditResponse(teacher_select, response_data, edit_controls = { save_button: { disabled: false }, read_only_notice: { hidden: true } }) {
    const teacher_query = {
        val(selected_value) { teacher_select.value = selected_value; return this; },
        change() { return this; }, trigger() { return this; },
    };
    vm.runInNewContext(handler_body, {
        res: { data: response_data }, Option: TeacherOption,
        document: { getElementById(element_id) {
            if (element_id === 'eteacher') return teacher_select;
            if (element_id === 'SubmitEditForm') return edit_controls.save_button;
            if (element_id === 'history-read-only-notice') return edit_controls.read_only_notice;
            assert.fail('Unexpected element: ' + element_id);
        } },
        $(selector) { assert.equal(selector, '#eteacher'); return teacher_query; },
    });
    return edit_controls;
}

test('ShowsTheRecordedTeacherWhenTheTeacherHasMovedOutsideCurrentBranchOptions', () => {
    const teacher_select = TeacherSelect();
    ApplyEditResponse(teacher_select, { teacher_id: 2, teacher: 'Recorded teacher <script>literal</script>' });
    assert.equal(teacher_select.value, '2');
    assert.equal(teacher_select.options.find(option => option.value === '2').text, 'Recorded teacher <script>literal</script>');
    assert.equal(teacher_select.options.find(option => option.value === '1').text, 'Current branch teacher');
});

test('RemovesThePreviousLessonsHistoricalTeacherOptionWhenAnotherLessonOpens', () => {
    const teacher_select = TeacherSelect();
    ApplyEditResponse(teacher_select, { teacher_id: 2, teacher: 'First historical teacher' });
    ApplyEditResponse(teacher_select, { teacher_id: 3, teacher: 'Second historical teacher' });
    assert.equal(teacher_select.value, '3');
    assert.equal(teacher_select.options.some(option => option.value === '2'), false);
    ApplyEditResponse(teacher_select, { teacher_id: '', teacher: 'No teacher recorded' });
    assert.equal(teacher_select.value, '');
    assert.deepEqual(teacher_select.options.map(option => option.value), ['', '1']);
});

test('ReadOnlyHistoricalLessonsExplainTheRestrictionAndDisableSaving', () => {
    const teacher_select = TeacherSelect();
    const edit_controls = ApplyEditResponse(teacher_select, { teacher_id: 1, teacher: 'Current branch teacher', is_read_only: true });
    assert.equal(edit_controls.save_button.disabled, true);
    assert.equal(edit_controls.read_only_notice.hidden, false);
    ApplyEditResponse(teacher_select, { teacher_id: 1, teacher: 'Current branch teacher', is_read_only: false }, edit_controls);
    assert.equal(edit_controls.save_button.disabled, false);
    assert.equal(edit_controls.read_only_notice.hidden, true);
});
