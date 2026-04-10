const err_msg = 'Something went wrong! Please try again after sometime.';

$(document).ready(function () {

    function change_all_annexure_status(current_ele_select, selectedAction)
    {
        if(current_ele_select.hasClass('has-annexure'))
        {
            const current_tr = current_ele_select.closest('tr');

            if (current_tr.length) {

                const next_tr = current_tr.next('tr');
                
                if (next_tr.hasClass('has-annexure-data')) {

                    next_tr.find('.form-select').each(function() {
                        $(this).val(selectedAction);
                    });
                }
            }

        }
    }

    function ajax_compliance(current_ele_select, obj, selectedAction) {

        const ele_parent = $(current_ele_select).parent();

        $( ele_parent.find('.reponse-status')[0] ).addClass('text-danger');

        let siteUrl = $(location).attr('href');

        // remove key
        siteUrl = siteUrl.replace("review-compliance", "");

        let request = $.ajax({
            url: siteUrl  + "save-status",
            type: "POST",
            data: { data: JSON.stringify(obj) },
            
            success: function(res) {

                try
                {
                    res = JSON.parse(res);

                    $( ele_parent.find('.reponse-status')[0] ).html(res.msg);

                    if(res.res == 'success')
                    {
                        if( selectedAction == '2' )
                        {
                            $( ele_parent.parent() ).removeClass('text-danger');

                            // function call
                            change_all_annexure_status(current_ele_select, selectedAction);
                        }
                        else
                        {
                            $( ele_parent.parent() ).addClass('text-danger');

                            // function call
                            change_all_annexure_status(current_ele_select, selectedAction);

                            if( obj.ans_type == 'annex')
                            {
                                const closest_parent = ele_parent.closest('table').closest('tr').prev('tr');

                                if(closest_parent.length > 0)
                                {
                                    const parent_select_annex = closest_parent.find('.reviewer-action .form-select');

                                    if(parent_select_annex.length > 0)
                                    {
                                        var status = ['2', '3'].includes(selectedAction.toString()) ? selectedAction.toString() : '2';
                                        
                                        parent_select_annex.val(status);
                                        parent_select_annex.closest('tr').addClass('text-danger');
                                    }
                                }
                            }
                        }

                        $( ele_parent.find('.reponse-status')[0] ).removeClass('text-danger');
                        $( ele_parent.find('.reponse-status')[0] ).addClass('text-success');
                    }

                }
                catch (error) {
                    $( ele_parent.find('.reponse-status')[0] ).html(err_msg);
                }
                
            },

            error: function(res) {
                $( ele_parent.find('.reponse-status')[0] ).html(err_msg);
            }

        });
    }

    if($('.reviewer-action').length > 0)
    {
        $('.reviewer-action select').on('change', function() {

            let select = $(this);
            
            let obj = {
                'ans_id' : $(select).attr('data-ansid'),
                'ans_type' : $(select).attr('data-anstype'),
                'action' : $(select).find(":selected").val(),
                'slctact' : $(select).attr('data-slctact')
            };

            //function call
            ajax_compliance( $(this), obj, $(select).find(":selected").val());
        });
    }

});