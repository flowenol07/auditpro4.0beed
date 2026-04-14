// rmk_err_msg = 'Something went wrong! Please try after sometime.';

function rmk_btn_click(btn)
{
    $('.audit-rmk-nav li').removeClass('active');
    $('.audit-rmk-multi-container').removeClass('show');
    btn.addClass('active');

    if(btn.attr('data-bs-target') !== undefined && $(btn.attr('data-bs-target')).length > 0)
        $(btn.attr('data-bs-target')).collapse('show');
}

function get_audit_rmk_data()
{
    if($('#audit_remark_container').length > 0)
    {
        $.ajax({
            url: $('#audit_remark_container').attr('data-url'),
            type: 'GET',
            success: function(res) {

                try 
                {
                    res = JSON.parse(res);

                    if(res.new !== undefined && res.new == true)
                        $('#assesment_remark').addClass('rmk-noti-bell');
                    else
                        $('#assesment_remark').removeClass('rmk-noti-bell');
                    
                    if(/*res.err == false &&*/ res.markup != '')
                    {
                        if(res.markup.current != '')
                            $('#audit_remark_current_container .rmk-res').html(res.markup.current);

                        if(res.markup.other != '')
                            $('#audit_remark_other_container .rmk-res').html(res.markup.other);
                    }
                    else
                    {
                        $('#audit_remark_current_container .rmk-res').html('');
                        $('#audit_remark_other_container .rmk-res').html('');
                    }                    
                } 
                catch (error) { alert(rmk_err_msg); }
            },
            
            error: function() { alert(rmk_err_msg); }
        });

    }
}

$(document).ready(function() {

    // function call
    get_audit_rmk_data();

    if($('#assesment_remark_form').length > 0)
    {
        $('#assesment_remark_form').on('submit', function(e){

            e.preventDefault();

            var form = $(this);
            var rmk_submit = $('#rmk_submit_btn');

            rmk_submit.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
    
                    // blank err
                    form.find('.rmk-span-err').text('');

                    try 
                    {
                        res = JSON.parse(res);
                        rmk_submit.removeAttr('disabled');
                        
                        if(res.err == true)
                        {
                            if(res.err_input !== undefined)
                            {
                                $.each(res.err_input, function(inputEle, errMsg) {

                                    var rmkSpanErr = $('#' + inputEle).parent().find('.rmk-span-err');

                                    if(rmkSpanErr.length > 0)
                                        $(rmkSpanErr).text(errMsg);
                                });
                            }
                        }
                        else
                        {
                            $(form).trigger("reset");

                            // function call
                            get_audit_rmk_data();

                            $('.audit-rmk-nav li').eq(1).on('click', function() {
                                rmk_btn_click($(this));
                            }).trigger("click");
                        }
                    } 
                    catch (error) { alert(rmk_err_msg); }
                },
                
                error: function() { alert(rmk_err_msg); }
            });

            return false;
            
        });
    }

    $(document).delegate('.rmk-subject-container', 'click', function() {
        
        let rmkSubjectClick = $(this);

        if(rmkSubjectClick.attr('data-href') !== undefined)
        {
            $.ajax({
                url: rmkSubjectClick.attr('data-href'),
                type: 'GET',
                success: function(res) {

                    try 
                    {
                        res = JSON.parse(res);

                        if(res.err == false)
                        {
                            rmkSubjectClick.removeAttr('data-href');
                            rmkSubjectClick.removeClass('rmk-read'); 
                        }
                    } 
                    catch (error) { alert(rmk_err_msg); }
                },
                
                error: function() { alert(rmk_err_msg); }
            });
        }
    });

    $(document).delegate('.rmk-remove-btn', 'click', function() {

        let link = $(this);

        if(confirm('Are you sure? you want to delete.'))
        {
            $.ajax({
                url: link.attr('href'),
                type: 'GET',
                success: function(res) {

                    try 
                    {
                        res = JSON.parse(res);

                        if(res.err == false)
                            link.parent().closest('.single-rmk-container').remove();
                        else
                            alert(res.msg);
                    } 
                    catch (error) { alert(rmk_err_msg); }
                },
                
                error: function() { alert(rmk_err_msg); }
            });
        }

        return false;
    });

    if($('.audit-rmk-nav li').length > 0)
    {
        $('.audit-rmk-nav li').eq(1).on('click', function() {
            rmk_btn_click($(this));
        }).trigger("click");

        $('.audit-rmk-nav li').on('click', function() {
            var btn = $(this);
            rmk_btn_click(btn);
        });
    }

});
