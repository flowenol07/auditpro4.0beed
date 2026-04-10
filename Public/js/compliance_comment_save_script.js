$(document).ready(function () {

    function ajax_compliance(current_ele_click, obj, filter_type = 1) {

        let err_msg = 'Something went wrong! Please try again after sometime.';
        let ele_parent = $(current_ele_click).parent();
        $( $(ele_parent).find('.reponse-status')[0] ).addClass('text-danger');

        let siteUrl = $(location).attr('href');

        if(filter_type == 1)
            siteUrl = siteUrl.split('compliance')[0] + 'compliance';
        else if(filter_type == 2)
            siteUrl = siteUrl.split('reviewer')[0] + 'reviewer';
        else
            return;

        let request = $.ajax({
            url: siteUrl + '/' + ( (filter_type == 1) ? 'save-compliance' : 'save-comment' ),
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
                        if(filter_type == 1)
                            $( $(ele_parent).parent() ).removeClass('text-danger');
                        
                        $( $(ele_parent).find('.reponse-status')[0] ).removeClass('text-danger');
                        $( $(ele_parent).find('.reponse-status')[0] ).addClass('text-success');
                    }

                    if( (obj.compliance != undefined && obj.compliance == '') || (obj.comment != undefined && obj.comment == '') )
                        $( $(ele_parent).parent() ).addClass('text-danger');
                }
                catch (error) { $( $(ele_parent).find('.reponse-status')[0] ).text(err_msg); }
                
            },

            error: function(res) { $( $(ele_parent).find('.reponse-status')[0] ).text(err_msg); }

        });
    }
    
    if($('.compliance-container').length > 0)
    {  
        $('.compliance-container .btn').on('click', function() {

            let textarea = $($(this).parent()).find('.form-control');
            
            let obj = {
                'ans_id' : $(textarea).attr('data-ansid'),
                'ans_type' : $(textarea).attr('data-anstype'),
                'compliance' : $(textarea).val(),
                'filter_type' : 1
            };

            //function call
            ajax_compliance($(this), obj);
        });
    }

    if($('.comment-container').length > 0)
    {
        $('.comment-container .btn').on('click', function() {

            let textarea = $($(this).parent()).find('.form-control');
            
            let obj = {
                'ans_id' : $(textarea).attr('data-ansid'),
                'ans_type' : $(textarea).attr('data-anstype'),
                'comment' : $(textarea).val(),
                'filter_type' : 2
            };

            //function call
            ajax_compliance($(this), obj, 2);
        });
    }

});