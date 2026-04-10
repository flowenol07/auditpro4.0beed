function validate_password_policy_markup()
{
    if($('#pwd_policy_container') != undefined) {
        
        let pwd_regex = $('#pwd_policy_container').attr('data-regex');
        let ul_items = $('#pwd_policy_container ul li');

        $('#password').on('input', () => {
        
            if(pwd_regex == null)
                return false;

            pwd_regex = new RegExp(pwd_regex);

            //single check 
            if(ul_items.length > 0) {

                ul_items.each(function() {
                    
                    if($(this).attr('data-regex') != undefined && $(this).attr('data-regex') != '') {

                        let c_pwd_regex = new RegExp($(this).attr('data-regex'));

                        if ( c_pwd_regex.test( $('#password').val()) )
                            $($(this).find('span')).addClass('strike-line');
                        else
                            $($(this).find('span')).removeClass('strike-line');
                    }
                });

                //default 
                if (pwd_regex.test($('#password').val()))
                    $($(ul_items[0]).find('span')).addClass('strike-line');
                else
                    $($(ul_items[0]).find('span')).removeClass('strike-line');

            }

        });
    }
    else
    {
        $('#pwd_policy_container').remove();
    }
}

//function call
validate_password_policy_markup();