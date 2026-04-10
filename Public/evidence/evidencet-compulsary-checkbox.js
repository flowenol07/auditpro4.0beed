$(document).ready(function() {
    
    $(document).delegate('.audit-evidence-chckbox, .compliance-evidence-chckbox', 'click', function() {

        let chkbox = $(this);
        let res_container = chkbox.parent().parent();
        let formData = { 
            'checkbox' : chkbox.is(":checked"),
            'ans_id' : chkbox.attr('data-ansid') != undefined ? chkbox.attr('data-ansid') : 0,
            'annex_id' : chkbox.attr('data-annexid') != undefined ? chkbox.attr('data-annexid') : 0,
            'ans_type' : 0
        };

        if(formData.ans_id == 0)
        {
            let questionRow = res_container.closest('.question-row');

            if(questionRow.length > 0)
                formData.ans_id = questionRow.attr('data-ansid') != undefined ? questionRow.attr('data-ansid') : 0;
        }

        if(chkbox.hasClass('audit-evidence-chckbox'))
            formData.ans_type = 1;
        else if(chkbox.hasClass('compliance-evidence-chckbox'))
            formData.ans_type = 2;

        $.ajax({
            url: chkbox.attr('data-ajxurl'),
            type: 'POST',
            data: formData,
            success: function(res) {

                try 
                {
                    res = JSON.parse(res);
                    let resSpan = $( res_container ).find('.res-span');

                    if(resSpan.length == 0)
                    {
                        $( res_container ).append('<span class="d-block res-span text-danger font-sm mt-2"></span>');
                        resSpan = $( res_container ).find('.res-span');
                    }
            
                    // has error
                    if(res.err)
                        $( resSpan ).html(res.msg);
                    else
                    {
                        // display markup
                        $( resSpan ).html(res.msg);
                        $( resSpan ).removeClass('text-danger');
                        $( resSpan ).addClass('text-success');
                        $(chkbox.parent()).find('span').text(res.markup);
                    }

                    setTimeout(function() {
                        $( resSpan ).remove();
                    }, 5000);
                } 
                catch (error) { alert(evi_err_msg); }
            },
            
            error: function() { alert(evi_err_msg); }
        });

        // return false;

    });
});