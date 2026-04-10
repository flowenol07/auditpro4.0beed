const cfErrMsg = 'Something went wrong! Please try again after sometime.';

function cf_ajax_comment(current_ele_click, obj, filter_type = 1) {

    let ele_parent = $(current_ele_click).parent();

    $( $(ele_parent).find('.reponse-status')[0] ).addClass('text-danger');

    let siteUrl = $(current_ele_click).closest('.audit-cf-container').attr('data-url');

    console.log(siteUrl);

    if(siteUrl == undefined || siteUrl == '')
        $( $(ele_parent).find('.reponse-status')[0] ).text(cfErrMsg);
    else
    {
        let request = $.ajax({
            url: siteUrl,
            type: "POST",
            data: { data: JSON.stringify(obj) },
            
            success: function(res) {

                // console.log(res);
                
                try
                {
                    res = JSON.parse(res);

                    $( $(ele_parent).find('.reponse-status')[0] ).html(res.msg);

                    if(res.res == 'success')
                    {   
                        $( $(ele_parent).find('.reponse-status')[0] ).removeClass('text-danger');
                        $( $(ele_parent).find('.reponse-status')[0] ).addClass('text-success');
                    }
                }
                catch (error) { $( $(ele_parent).find('.reponse-status')[0] ).text(cfErrMsg); }
                
            },

            error: function(res) { $( $(ele_parent).find('.reponse-status')[0] ).text(cfErrMsg); }

        });
    }
}

$(document).ready(function () {
    
    if($('.audit-cf-container').length > 0)
    {  
        $('.audit-cf-container .btn').on('click', function() {

            let textarea = $($(this).parent()).find('.form-control');
            
            let obj = {
                'ans_id' : $(textarea).attr('data-ansid'),
                'compliance' : $(textarea).val()
            };

            //function call
            cf_ajax_comment($(this), obj);
        });
    }

});