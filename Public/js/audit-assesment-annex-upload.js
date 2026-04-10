const annex_err_msg = 'Something went wrong! Please try after sometime.';

function annex_csv_loader(count = '', df = false) {

    if( !df ) count = (count != '') ? ('Uploading: <span class="font-bold">'+ count +'%</span>') : 'Uploading...';
    return '<span class="annex-csv-upload-loader">'+ count +'</span>';
}

$(document).ready(function() {
    
    if($('.download-sample-annex').length > 0)
    {
        $('.download-sample-annex').on('click', function() {

            var dsaBtn = $(this);

            if(dsaBtn.attr('data-annexurl') !== undefined && dsaBtn.attr('data-annexurl') != '') {

                var passData = {
                    annexid: dsaBtn.data('annexid') || null,
                    ansid: dsaBtn.data('ansid') || null
                };

                $.ajax({
                    url: dsaBtn.attr('data-annexurl'),
                    method: 'POST',
                    data: passData,
                    xhrFields: { responseType: 'blob' },
                    success: function(data) {

                        try {

                            url = window.URL.createObjectURL(data);
                            
                            const a = $('<a></a>')
                                .attr('href', url)
                                .attr('download', 'sample-annexure.csv')
                                .css('display', 'none')
                                .appendTo('body');
                            a[0].click();
                            window.URL.revokeObjectURL(url);
                            a.remove();
                            
                        } catch (error) { alert(annex_err_msg); }

                        
                    },
                    error: function() { alert(annex_err_msg); }
                });
            }

            return false;
        });
    }

    if($('.annex-csv-upload-btn').length > 0)
    {
        $('.annex-csv-upload-btn').on('click', function() {

            const c_btn = $(this);
            const res_container = $(c_btn).parent().find('.annex-csv-upload-container');
            const form = $('#annex_csv_upload_form');
            const fileInput = form.find('#annex_csv_file');
            const actionURL = form.attr("data-action");

            if( c_btn.attr('data-quesid') !== undefined && c_btn.attr('data-quesid') != '' && 
                c_btn.attr('data-dumpid') !== undefined && actionURL != undefined)
            {
                // push data in text box
                $('#annex_csv_quesid').val(c_btn.attr('data-quesid'));
                $('#annex_csv_dumpid').val(c_btn.attr('data-dumpid'));

                fileInput.click();

                fileInput.off('change').on('change', function() {

                    if (this.files.length > 0)
                    {
                        const annexCSVFile = this.files[0];

                        // check file type and size // Check if file is selected
                        if (!annexCSVFile) {
                            res_container.html('<span class="d-block text-danger font-sm mt-2">Please select a file.</span>');
                            return;
                        }

                        if (!annex_csv_file_type.includes(annexCSVFile.type)) {
                            res_container.html('<span class="d-block text-danger font-sm mt-2">Only '+ annex_csv_file_type +' files are allowed.</span>');
                            return;
                        }

                        const annexCSVMaxSize = annex_csv_file_size * 1024 * 1024;

                        if (annexCSVFile.size > annexCSVMaxSize) {
                            res_container.html('<span class="d-block text-danger font-sm mt-2">File size exceeds the maximum limit of '+ annex_csv_file_size +' MB.</span>');
                            return;
                        }

                        const formData = new FormData(form[0]);
                        res_container.html( annex_csv_loader('0') );

                        $.ajax({
                            url: actionURL,
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            xhr: function() {
                                var xhr = new window.XMLHttpRequest();
                                xhr.upload.addEventListener('progress', function(e) {
                                    if (e.lengthComputable) {
                                        var percentComplete = e.loaded / e.total * 100;
                                        res_container.html( annex_csv_loader(percentComplete) );
                                    }
                                }, false);
                                return xhr;
                            },
            
                            success: function(res) {

                                try 
                                {
                                    res = JSON.parse(res);

                                    // has error
                                    if(res.err == false)
                                    {
                                        res_container.html('');

                                        // find parent table and append
                                        const annex_table = res_container.parent().find('table');
                                        
                                        if(annex_table.length > 0 && res.markup !== undefined && res.markup != '')
                                            annex_table.append(res.markup);
                                    }
                                    else if(res.markup !== undefined && res.markup != '')
                                    {
                                        // display markup
                                        res_container.html('<span class="d-block text-danger font-sm mt-2 mb-2">'+ res.msg +'</span>');
                                        
                                        if(res.markup !== undefined && res.markup != '')
                                            res_container.append(res.markup);

                                        c_btn.hide();
                                    }
            
                                } 
                                catch (error) { alert(annex_err_msg); }
                            },
                            
                            error: function() { alert(annex_err_msg); },

                            complete: function() {
                                // Reset file input and form fields
                                fileInput.val('');
                                $('#annex_csv_quesid').val('');
                                $('#annex_csv_dumpid').val('');
                            }
                        });

                    }

                });
            }
            else
                alert(annex_err_msg);

        });
    }

    // revoke form submit
    if($('#annex_csv_upload_form').length > 0) {
        $('#annex_csv_upload_form').on('submit', function() { return false; });
    }
});