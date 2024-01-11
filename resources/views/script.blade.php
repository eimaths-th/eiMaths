<script language="javascript" type="text/javascript">
    $.datepicker.setDefaults($.datepicker.regional['en']);

    let currentYear, currentMonth; // Declare variables globally

    function datesearch() {
        currentYear = new Date().getFullYear();
        currentMonth = new Date().getMonth();
    }

    function retrieveFieldValues() {
        var savedYear = localStorage.getItem('Yearsave');
        var savedMonth = localStorage.getItem('Monthsave');
        var savedSearchCentre = localStorage.getItem('searchCentre');

        // Set field values from local storage
        if (savedYear && savedMonth) {
            currentYear = savedYear;
            currentMonth = savedMonth;
            daterange();
        } else {
            datesearch();
        }
        if (savedSearchCentre) {
            $('#dcentre').val(savedSearchCentre);
        }
    }

    function daterange() {
        if (!currentYear || !currentMonth) {
            datesearch();
        }
        $('#reservation').datepicker({
            dateFormat: 'yy-mm',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            yearRange: '2023:' + currentYear,
            onClose: function(dateText, inst) {
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                storeFieldValues();
            }
        }).datepicker('setDate', new Date(currentYear, currentMonth, 1));
    }

    // Attach event handler to a button or element to trigger the reset
    $('#resetSearchButton').on('click', async function() {
        localStorage.removeItem('Yearsave');
        localStorage.removeItem('Monthsave');
        localStorage.removeItem('searchCentre');

        // Set field values to empty
        $('#dcentre').val('1');
        localStorage.setItem('searchCentre', 1);

        // Set the date range back to its default
        datesearch();
        daterange();
    });

    function storeFieldValues() {
        var Yearstart = $('#reservation').val();
        var searchCentre = $('#dcentre').val();

        // Split Yearstart into Yearsave and Monthsave
        var [Yearsave, Monthsave] = Yearstart.split('-');

        // Store values in local storage
        localStorage.setItem('Yearsave', Yearsave);
        localStorage.setItem('Monthsave', Monthsave - 1);
        localStorage.setItem('searchCentre', searchCentre);
    }

    $('#dcentre').on('change', storeFieldValues);

    // Call the function to set initial field values on page load
    retrieveFieldValues();
    // Initialize date range on page load
    daterange();
</script>
