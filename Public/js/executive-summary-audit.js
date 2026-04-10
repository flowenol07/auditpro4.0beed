$(document).ready(function() {

    function totalCalc(type) {
        let current_value = '.' + type + '_current_value';
        let total_current = '#' + type + '_total_current';
        let type_total = '#' + type + '_total';
        let ytd_total = '#ytm_id_' + type;

        let total = 0;

        $(current_value).each(function() {
            let value = $(this).val() || $(this).text();
            total += Number(value) || 0; // Default to 0 if value is not a number
        });

        if (isNaN(total)) {
            $(total_current).html('0.00');
        } else {
            $(total_current).html(parseFloat(total).toFixed(2));
        }

        let total_march = Number($(type_total).text()) || 0;
        let ytd = total - total_march;

        if (isNaN(ytd)) {
            $(ytd_total).html('0.00');
        } else {
            $(ytd_total).html(parseFloat(ytd).toFixed(2));
        }
    }

    function total_ytd() {
        totalCalc('deposits');
        totalCalc('advances');
        totalCalc('npa');
    }

    if ($('.deposits_current_value').length > 0) {
        $('.deposits_current_value').on('input', function() {
            total_ytd();
        });
    }

    if ($('.advances_current_value').length > 0) {
        $('.advances_current_value').on('input', function() {
            total_ytd();
        });
    }

    if ($('.npa_current_value').length > 0) {
        $('.npa_current_value').on('input', function() {
            total_ytd();
        });
    }

    function calc_per_employee() {
        let advances_total = Number($('#advances_total_current').text()) || 0;
        let deposits_total = Number($('#deposits_total_current').text()) || 0;
        let staff_count = $('#staff_count').val() || $('#staff_count').text() || 0;

        if (advances_total === 0 || deposits_total === 0 || staff_count === 0) {
            $("#per_emp_business").html('0.00');
        } else {
            let perEmpBusiness = (advances_total + deposits_total) / Number(staff_count);
            if (isNaN(perEmpBusiness) || !isFinite(perEmpBusiness)) {
                $('#per_emp_business').html('0.00');
            } else {
                $("#per_emp_business").html(parseFloat(perEmpBusiness).toFixed(2));
            }
        }
    }

    $('.advances_current_value, .deposits_current_value, #staff_count').on('input', function() {
        calc_per_employee();
    });

    function cd_ratio() {
        let deposit_total = Number($('#deposits_total_current').text()) || 0;
        let advances_total = Number($('#advances_total_current').text()) || 0;

        if (deposit_total === 0) {
            $("#cd_ratio").html('0.00');
        } else {
            let cd_ratio = (advances_total / deposit_total) * 100;
            $("#cd_ratio").html(parseFloat(cd_ratio).toFixed(2));
        }
    }

    $('.advances_current_value, .deposits_current_value').on('input', function() {
        cd_ratio();
    });

    cd_ratio();
    calc_per_employee();
    total_ytd();
});
