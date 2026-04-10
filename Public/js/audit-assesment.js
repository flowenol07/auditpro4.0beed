const err_msg = 'Something went wrong! Please try after sometime.';

function generate_question_obj(container, formData)
{
    let questionRow = null;

    if($(container).hasClass('question-row'))
        questionRow = $(container);
    else
        questionRow = $(container).find('.question-row');

    let answersObj = {};

    if(questionRow.length > 0)
    {
        $(questionRow).each(function(i, obj) {

            if( Object.keys(formData).length > 0 )
            {
                let errSpan = $(obj).find('.audit-question-err');

                // check for compliance
                if(formData[ $(obj).attr('data-ques') ].is_compliance == true)
                    $( $(obj).find('.compliance-chckbox') ).prop('checked', true);

                // ansid
                if( formData[ $(obj).attr('data-ques') ] !== undefined && 
                    formData[ $(obj).attr('data-ques') ].ansid !== undefined )
                    $(obj).attr('data-ansid', formData[ $(obj).attr('data-ques') ].ansid);

                if( formData[ $(obj).attr('data-ques') ] !== undefined && 
                    formData[ $(obj).attr('data-ques') ].err )
                {
                    $(errSpan).show();
                    $(errSpan).text( formData[ $(obj).attr('data-ques') ].err );
                }
                else    
                {
                    $(errSpan).hide(); $(errSpan).text('');
                }
            }
            else
            {
                answersObj[ $(obj).attr('data-ques') ] = {
                    id : $(obj).attr('data-ques'), header_id : $(obj).attr('data-headerid'),
                    answer_given : null, is_compliance : 0, audit_comment : null
                };
                
                // find form select
                let formSelect = $(obj).find('.form-select');

                if( formSelect.length > 0 )
                    answersObj[ $(obj).attr('data-ques') ].answer_given = $(formSelect).find(":selected").val().trim();

                // find compliance checkbox
                let complianceChckbox = $(obj).find('.compliance-chckbox');

                if( complianceChckbox.length > 0 )
                    answersObj[ $(obj).attr('data-ques') ].is_compliance = $(complianceChckbox).is(':checked');

                // find evidence checkbox
                let ccEviChckbox = $(obj).find('.compliance-evidence-chckbox');

                if( ccEviChckbox.length > 0 )
                    answersObj[ $(obj).attr('data-ques') ].cc_evi_upload = $(ccEviChckbox[0]).is(':checked');

                // find text area
                let auditComment = $(obj).find('.audit-comment');

                if(auditComment.length > 0 && $(auditComment).val().trim() != '')
                    answersObj[ $(obj).attr('data-ques') ].audit_comment = $(auditComment).val().trim();
            }

        });
    }
    
    return answersObj;
    
}

function annex_row_save_func(thisClick)
{
    let cSaveBtn = $(thisClick);
    let parentTr = $(thisClick).parent().parent();
    let formData = {};

    if($(cSaveBtn).attr('data-annexid') !== undefined)
        formData[ 'annex_id' ] = $(cSaveBtn).attr('data-annexid');

    if($(parentTr).find('.form-control').length > 0)
    {
        $(parentTr).find('.form-control').each(function(i, obj) {

            let key = i;

            if($(obj).hasClass('br'))
                key = 'br';
            else if($(obj).hasClass('cr'))
                key = 'cr';
            else if($(obj).hasClass('rt'))
                key = 'rt';

            formData[ key ] = $(obj).val();
        });

        let ccEviChckbox = $(parentTr).find('.compliance-evidence-chckbox');

        if( ccEviChckbox.length > 0 )
            formData['cc_evi_upload'] = $(ccEviChckbox[0]).is(':checked');
    }

    let annexContainer = $(thisClick).closest('.annex-container');

    // get question details
    let questionContainer = $(annexContainer).parent();
    let saveAnnex = true;
    
    if(!annexContainer.length > 0 || !questionContainer.length > 0)
        saveAnnex = false;
    else
    {
        let answersObj = generate_question_obj(questionContainer, {});

        if(!Object.keys(answersObj).length > 0)
            saveAnnex = false;
        else // function call
            send_to_save(cSaveBtn, annexContainer, { data_annex : formData, data_annex_ques : answersObj });
    }

    if(!saveAnnex) $(annexContainer).find('.annex-container-err').text(err_msg);

    return false;
}

function annex_row_remove(thisClick)
{
    let annexContainer = $(thisClick).closest('.annex-container');
    let resSpan = $(annexContainer).find('.annex-container-err');

    $( resSpan ).addClass('text-danger');
    $( resSpan ).hide();

    if($(thisClick).attr('data-annexid') === undefined)
    {
        $( resSpan ).text(err_msg);
        $( resSpan ).show();
        return;
    }

    let url = $(location).attr('href');
    url = url.split("category/")[0];
    let clickBtn = $(thisClick);

    $.ajax({

        url: url + 'remove-annex',
        type: "POST",
        data: { annex_id : $(thisClick).attr('data-annexid') },
        
        success: function(res) {

            // console.log(res);

            try
            {
                res = JSON.parse(res);                        

                // has errors
                $( resSpan ).html(res.msg);
                $( resSpan ).show();

                if(res.success !== undefined)
                {
                    $( resSpan ).removeClass('text-danger');
                    $( resSpan ).addClass('text-success');

                    // remove row
                    $(clickBtn).parent().parent().remove();

                    setTimeout(function() {
                        $( resSpan ).hide();
                        $( resSpan ).html('');
                        $( resSpan ).addClass('text-danger');
                        $( resSpan ).removeClass('text-success');
                    }, 5000);
                }

            }
            catch (error) {
                $( resSpan ).text(err_msg);
                $( resSpan ).show();
            }
            
        },

        error: function(res) {
            $( resSpan ).text(err_msg);
            $( resSpan ).show();
        }

    });
}

// function for subset container
function change_subset_container(this_obj)
{
    if( $('.subset-type-select').length > 0 )
    {
        let cQues = $(this_obj).attr('data-ques');

        if(cQues != '')
        {
            $('.subset-container-' + cQues).addClass('d-none');

            if($(this_obj).val() != '')
            {
                let subset = $('.subset-' + $(this_obj).val() + 'Ques' + cQues);
                
                if(subset.length > 0)
                    $(subset).removeClass('d-none');
            }
        }
    }        
}

// function for annex container
function toggle_annex_container(this_obj)
{
    let cQues = $(this_obj).attr('data-ques');

    if(cQues != '')
    {
        let annexContainer = $(this_obj).parent().parent();

        if($(annexContainer).find('.annex-container').length > 0)
            $(annexContainer).find('.annex-container').addClass('d-none');

        if($(this_obj).val() != '' && $(annexContainer).find('.annex-' + $(this_obj).val() + 'Ques' + cQues).length > 0)
            $(annexContainer).find('.annex-' + $(this_obj).val() + 'Ques' + cQues).removeClass('d-none');
    }
}

function generate_annex_markup(cSaveBtn, container, markup) {

    // append to table
    if($(cSaveBtn).hasClass('annex-add-row'))
    {
        $(container).find('table tbody').append(markup);
        $($(cSaveBtn).parent().parent()).find('.form-control').val('');
    }
}

function send_to_save(cSaveBtn, container, formData) {

    let resSpan = null;

    // for annex save
    if(formData.data_annex != undefined)
        resSpan = $(container).find('.annex-container-err');
    else
        resSpan = $(container).find('.save_response')[0];

    $( resSpan ).addClass('text-danger');
    $( resSpan ).hide();

    $.ajax({

        url: $(location).attr('href'),
        type: "POST",
        data: formData,
        
        success: function(res) {

            // console.log(res);

            try
            {
                res = JSON.parse(res);                        

                // has errors
                if( res.data && Object.keys(res.data).length > 0)
                    generate_question_obj(container, res.data);
                if( res.data_annex && res.data_annex_markup && Object.keys(res.data_annex).length > 0)
                    generate_annex_markup(cSaveBtn, container, res.data_annex_markup);

                $( resSpan ).html(res.msg);
                $( resSpan ).show();

                if(res.success !== undefined)
                {
                    // change to update answer
                    if(cSaveBtn.text() !== 'Update Answers' && res.data_annex === undefined)
                        cSaveBtn.text('Update Answers');
                    
                    $( resSpan ).removeClass('text-danger');
                    $( resSpan ).addClass('text-success');

                    let cardBody = $(resSpan).parent().parent();

                    $(cardBody).find('.question-row').addClass('bg-ques-opac');
                    $(cardBody).find('.question-row').removeClass('text-danger');

                    if($(cardBody).find('.compliance-chckbox:checked').length > 0)
                    {
                        $(cardBody).find('.compliance-chckbox:checked').each(function(i, obj) {
                            $($(obj).closest('.question-row')).removeClass('bg-ques-opac');
                            $($(obj).closest('.question-row')).addClass('text-danger');
                        });
                    }

                    setTimeout(function() {
                        $( resSpan ).hide();
                        $( resSpan ).html('');
                        $( resSpan ).addClass('text-danger');
                        $( resSpan ).removeClass('text-success');
                    }, 5000);
                }

            }
            catch (error) {
                $( resSpan ).text(err_msg);
                $( resSpan ).show();
            }
            
        },

        error: function(res) {
            $( resSpan ).text(err_msg);
            $( resSpan ).show();
        }

    });

}

$(document).ready(function() {

    // subset-type-select-option
    if( $('.subset-type-select').length > 0 )
    {
        $('.subset-type-select').each(function() {

            // default call // function call
            change_subset_container(this);

            $(this).on('change', function() {
            
                // function call
                change_subset_container(this);

            });
        });    
    }

    // annex-type-select
    if( $('.annex-type-select').length > 0 )
    {
        $('.annex-type-select').each(function() {

            // default call // function call
            toggle_annex_container(this);

            $(this).on('change', function() {
            
                // function call
                toggle_annex_container(this);

            });
        });    
    }

    if($('.save-answers').length > 0)
    {
        $('.save-answers').on('click', function() {

            let cSaveBtn = $(this);

            // check every question in card body
            let container = $(this).parent().parent();
            
            // function call
            let answersObj = generate_question_obj(container, {});

            if(Object.keys(answersObj).length > 0) //function call
                send_to_save(cSaveBtn, container, { data : answersObj });
        });

        if($('.compliance-chckbox').length > 0)
        {
            $('.compliance-chckbox').on('change', function() {
                if(!$(this).is(':checked')) $(this).removeAttr( "checked" );
            });
        }
    }

    $(document).delegate('.annex-add-row, .annex-row-update', 'click', function() {
        annex_row_save_func($(this)); // function call        
    });

    $(document).delegate('.annex-row-remove', 'click', function() {

        if(confirm('Are you sure? you want to delete.'))
            annex_row_remove($(this)); // function call
    });

    if($('.audit-ques-default-btn').length > 0)
    {
        $('.audit-ques-default-btn').on('click', function() {

            let headerContainer = $(this).parent();

            if($(headerContainer).find('.form-select').length > 0)
            {
                $(headerContainer).find('.form-select').each(function(i, obj) {

                    if($(obj).find('option').length > 0)
                    {
                        let optionFound = false;

                        $(obj).find('option').each(function(j, obj) {

                            if ($(this).data('def') == 1) {
                                $(this).prop('selected', true);
                                optionFound = true;
                            }
                        });

                        if(!optionFound && $(obj).find('option').length > 1)
                            $(obj).find('option').eq(1).prop('selected', true);

                        if($(obj).hasClass('subset-type-select')) {                           
                            // default call // function call
                            change_subset_container($(obj));
                        }
                        else if($(obj).hasClass('annex-type-select')) {                           
                            // default call // function call
                            toggle_annex_container($(obj));
                        }
                    }

                });
            }
        });
    }

    if( $('.search_acc_audit').length > 0 && $('#search_acc_autocomplete').length > 0 )
    {
        $('#search_acc_autocomplete').val('');
        $('#search_acc_autocomplete').removeClass('d-none');

        $('#search_acc_autocomplete').on('input', function() {

            if($(this).val().length > 0) {

                let search_acc_div = new RegExp( $(this).val() );

                $('.search_acc_audit').each(function(i, obj) {

                    let data_key = 'data-account_no';

                    if( $(obj).attr( data_key ) != undefined )
                    {
                        if( search_acc_div.test($(obj).attr( data_key )) )
                            $(obj).removeClass('d-none');
                        else
                            $(obj).addClass('d-none');
                    }

                });
            }
            else
                $('.search_acc_audit').removeClass('d-none');
        });
    }
    else
        $('#search_acc_autocomplete').addClass('d-none');

    // 21.09.2024 // kunal update
    if( $('.search_acc_audit').length > 0 && 
        $('.account-tab-container').length > 0 )
    {
        const container = document.querySelector('.account-tab-container');
        const targetLink = container.querySelector('a.btn-primary'); 

        if (targetLink) {
            targetLink.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    }

    if( $('#accDefaultAnswers').length > 0 && 
        $('.audit-ques-container:not(".d-none")').length > 0 && 
        $('.audit-ques-default-btn').length > 0 )
    {
        $('#accDefaultAnswers').on('click', function() {

            if($('.audit-ques-container:not(".d-none")').find('.audit-ques-default-btn').length > 0) {
                $('.audit-ques-container:not(".d-none")').find('.audit-ques-default-btn').trigger('click');
            }
            
        });
    }

    if( $('#accMarkAsComplete').length > 0 && $('.audit-ques-container:not(".d-none")').length > 0)
    {
        $('#accMarkAsComplete').on('click', function() {
            
            let questionRowCnt = $('.audit-ques-container:not(".d-none")').find('.question-row').length;
            let questionRowAnsCnt = $('.audit-ques-container:not(".d-none")').find('.question-row.bg-ques-opac').length;

            if( questionRowCnt != questionRowAnsCnt && 
                !confirm('Assesment is not yet completed. Are you sure you want to set other questions mark as the default in the current account?') )
                return false;

        });
    }

    if( $('#allAccAssesmentComplete').length > 0)
    {
        $('#allAccAssesmentComplete').on('click', function() {

            if( !confirm('Are you sure you want to complete the assesment of all accounts in the current category?') )
                return false;

        });
    }
});