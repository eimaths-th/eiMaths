(function ($) {
    "use strict";
    $(document).ready(function () {

        //referral duration
        let referralDurationDiv = $('.referral_duration_div');

        let referralDurationType = $('.referral_duration_type:checked').val();

        if (referralDurationType === 'Fixed') {
            referralDurationDiv.show();
        } else {
            referralDurationDiv.hide();
        }
        $(document).on('change', '.referral_duration_type', function (event) {
            let referral_duration_type = $(this).val();
            if (referral_duration_type === 'Fixed') {
                referralDurationDiv.show();
            } else {
                referralDurationDiv.hide();
            }
        });


        $(document).on('change', '.commission_type', function (event) {
            let type = $(this).val();
            if (type === 'Flat') {
                $('.commissionTypePercentage').addClass('d-none');
                $('.commissionTypeAmount').removeClass('d-none');
            } else {
                $('.commissionTypePercentage').removeClass('d-none');
                $('.commissionTypeAmount').addClass('d-none');
            }
        });

        $(document).on('change', '.communication_approval_system', function (event) {
            let type = $(this).val();
            if (type === 'cron') {
                $('.cronjobDiv').removeClass('d-none');
            } else {
                $('.cronjobDiv').addClass('d-none');
            }
        });

    });
})(jQuery);
