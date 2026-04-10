function reports_filter_common_js(filterCount, totalTdCount, filter1Id, td1Posi, filter2Id = '', td2Posi = 0, filter3Id = '', td3Posi = 0)
{
    $(document).ready( function () {
        
        $("#filterBtn").on("click", function() {

            let filter1 = $("#" + filter1Id + " option:selected");
            let filter2 = $("#" + filter2Id + " option:selected");
            let filter3 = $("#" + filter3Id + " option:selected");

            tr = $("#employeeDataTable tbody").find("tr");

            if(tr.length > 0)
            {
                $(tr).each( function(i, obj) {
                    let tds = $(obj).find("td");
                    
                    if(tds.length == totalTdCount)
                    {
                        let hideTr = true;

                        if(filterCount ==  3)
                        {
                            const filter1Var = filter1.text() == $($(tds)[td1Posi]).text();
                            const filter2Var = filter2.text() == $($(tds)[td2Posi]).text();
                            const filter3Var = filter3.text() == $($(tds)[td3Posi]).text();

                            if(filter1.val() != "" && filter2.val() != "" && filter3.val() != "")
                            {
                                if(filter1Var && filter2Var && filter3Var)
                                    hideTr = false;
                            }
                            else if((filter1.val() != "" && filter2.val() != "") || (filter3.val() != "" && filter1.val() != "") || (filter3.val() != "" && filter2.val() != ""))
                            {
                                if((filter1Var && filter2Var && filter3Var) || (filter1Var && filter3Var) || (filter1Var && filter2Var) || (filter3Var && filter2Var))
                                    hideTr = false;
                            }
                            else if(filter1.val() != "")
                            {
                                if(filter1Var)
                                    hideTr = false;
                            }
                            else if(filter3.val() != "")
                            {
                                if(filter3Var)
                                    hideTr = false;
                            }
                            else if(filter2.val() != "")
                            {
                                if(filter2Var)
                                    hideTr = false;
                            }
                            else
                            {   
                                hideTr = false;

                            }
                        }

                        if(filterCount ==  2)
                        {
                            if(filter1.val() != "" && filter2.val() != "")
                            {
                                if(filter1.text() == $($(tds)[2]).text() && filter2.text() == $($(tds)[3]).text())
                                    hideTr = false;
                            }
                            else if(filter1.val() != "" || filter2.val() != "")
                            {
                                if(filter1.text() == $($(tds)[2]).text() || filter2.text() == $($(tds)[3]).text())
                                    hideTr = false;
                            }
                            else
                            {   
                                hideTr = false;
                            }
                        }

                        if(filterCount ==  1)
                        {
                            if(filter1.val() != "")
                            {
                                if(filter1.text() == $($(tds)[td1Posi]).text())
                                    hideTr = false;
                            }
                            else  
                                hideTr = false;
                        }

                        if(hideTr)
                            $(obj).addClass("d-none");
                        else
                            $(obj).removeClass("d-none");
                    }   
                });
            }
        });
    });
}