(function () {
    'use strict';
    const feedback = document.getElementById('credit-note-feedback');
    if (!feedback) return;
    const table = document.querySelector('.credit-note-table');
    if (table) table.addEventListener('error', function () {
        feedback.textContent = 'Credit Note register could not be refreshed.';
    });
}());
