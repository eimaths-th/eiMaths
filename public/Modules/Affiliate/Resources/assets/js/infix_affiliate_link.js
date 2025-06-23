(function ($) {
    "use strict";
    let _token = $('meta[name="csrf-token"]').attr('content');
    if (typeof _token == 'undefined') {
        _token = $('#csrf_token').val();
    }
    $(document).ready(function () {
        $(document).on('input', '#url', function (event) {
            let url = $(this).val();
            let userName = $('#user_name').val();
            let affiliateUrl = url + '?ref=' + userName;
            $('#affiliate_link').val(affiliateUrl);
        });

        $(document).on('click', '#withdraw_request_btn', function (event) {
            let minimumWithdrawAmount = parseFloat($('#minimum_withdraw_amount').val());
            let userBalance = parseFloat($('#user_balance').val());
            if (userBalance < minimumWithdrawAmount) {
                toastr.info('Minimum Payout is ' + minimumWithdrawAmount + '. Your current balance ' + userBalance)
            } else {
                $('#withdraw_request_modal').modal('show');
            }
        });

        $(document).on('keyup', '#withdraw_amount', function () {
            let withdrawAmount = parseFloat($(this).val());
            let userBalance = parseFloat($('#user_balance').val());
            let minimumWithdrawAmount = parseFloat($('#minimum_withdraw_amount').val());
            if (withdrawAmount > userBalance) {
                $('#withdraw_amount_msg').html('Insufficient Balance');
                $('#withdraw_submit_btn').prop('disabled', true);
                $(this).css({
                    'border': '1px solid red'
                });
                $(this).focus();
            } else if (withdrawAmount < minimumWithdrawAmount) {
                $('#withdraw_amount_msg').html("You Can't Payout Less Then Minimum Payout " + minimumWithdrawAmount);
                $('#withdraw_submit_btn').prop('disabled', true);
                $(this).css({
                    'border': '1px solid red'
                });
                $(this).focus();
            } else {
                $('#withdraw_amount_msg').html('');
                $('#withdraw_submit_btn').prop('disabled', false);
                $(this).css({
                    'border': "1px solid #eceef4"
                });
                $(this).focusout();
            }
        });

        $(document).on('submit', '#create_withdraw_request', function (event) {

            console.log(_token);
            console.log($('#withdraw_request_store_url').val());

            event.preventDefault();
            let formElement = $(this).serializeArray()
            let formData = new FormData();
            formElement.forEach(element => {
                formData.append(element.name, element.value);
            });

            formData.append('_token', _token);
            resetValidationError();
            $.ajax({
                url: $('#withdraw_request_store_url').val(),
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    create_form_reset();
                    $('#withdraw_request_modal').modal('hide');
                    window.location.reload();
                    toastr.success('Withdraw Request Submitted Successfully', 'Success');
                },
                error: function (response) {

                    if (response.status === 404 && response.responseJSON.error) {
                        create_form_reset();
                        $('#withdraw_request_modal').modal('hide');
                        $('#withdraw_submit_btn').prop('disabled', false);
                        toastr.error(response.responseJSON.error);

                    } else if (response.status === 503) {
                        $('#withdraw_submit_btn').prop('disabled', false);
                        toastr.error('Service unavailable. Please try again later.');
                    } else {
                        $('#withdraw_submit_btn').prop('disabled', false);
                        toastr.error('An unexpected error occurred.');
                    }

                    // Handle validation errors, if necessary
                    if (response.responseJSON && response.responseJSON.errors) {
                        showValidationErrors('#create_withdraw_request', response.responseJSON.errors);
                    }
                }
            });
        });


        //tab active
        $('a[data-bs-toggle="tab"]').on('show.bs.tab', function (e) {
            localStorage.setItem('activeAffiliateTab', $(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeAffiliateTab');

        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }

        //end tab active


        function create_form_reset() {
            $('#create_withdraw_request')[0].reset();
            $(".primary_select").niceSelect('update');
        }

        function showValidationErrors(formType, errors) {
            $(formType + ' #error_withdraw_amount').text(errors.withdraw_amount);
            $(formType + ' #error_payment_type').text(errors.payment_type);
        }

        function resetValidationError() {
            $('#error_withdraw_amount').html('');
            $('#error_payment_type').html('');
        }

        $(document).on('click', '#create_affiliate_link_modal_btn', function (event) {
            $('#create_affiliate_link_modal').modal('show');
        });

        // open paypal modal

        $(document).on('click', '#affiliate_add_paypal_modal_btn', function (event) {
            $('#affiliate_add_paypal_modal').modal('show');
        });

        $(document).on('click', '.affiliate_buttons .copy_link', function (event) {
            const link = $(this).data('link');

            $('<input>').val(link).appendTo('body').select()
            document.execCommand('copy')
            toastr.success('Link Copied Successfully', 'Success');
            console.log(link)
            /*         function copyCurrentUrl() {
                         var copyText = window.location.href;
                         var dummy = $('<input>').val(copyText).appendTo('body').select()
                         document.execCommand('{{ __("common.Copy") }}')
                         setTimeout(function () {
                             toastr.success('Link Copied Successfully', 'Success Alert', {
                                 "iconClass": 'customer-info'
                             }, {
                                 timeOut: 2000
                             });
                         }, 500);
                     }
                     */
        });


    });
})(jQuery);
