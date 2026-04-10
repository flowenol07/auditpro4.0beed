$(document).ready(function () {
  if (
    $("#reportAuditUnit, #reportHOAuditUnit").length > 0 &&
    $("#reportAuditAssesment").length > 0
  ) {
    const err_msg = "Something went wrong! Please try after sometime.";

    function get_assesment_data(this_obj)
    {
        let reportAuditUnit = $(this_obj);
        let resSpan = reportAuditUnit.parent().find(".text-danger");
  
        $( resSpan ).html('');
        $('#reportAuditAssesment option:not(:first-child)').remove();
  
        if ( reportAuditUnit.val().length > 0 &&
             reportAuditUnit.attr("data-url").length > 0 )
        {    

          $postData = { audit_unit_id: reportAuditUnit.val() };
          
          if($('#complianceNeeded').length > 0)
            $postData['complianceNeeded'] = true;

          if($('#pendingReCompliance').length > 0)
            $postData['pendingReCompliance'] = true;

          $.ajax({
  
            url: reportAuditUnit.attr("data-url"),
            type: "POST", data: $postData,
  
            success: function (res) {
  
              try {
  
                res = JSON.parse(res);
  
                if(res.data !== undefined && res.success !== undefined)
                {
                  $.each(res.data, function(index, dataObj) {
                      $('#reportAuditAssesment').append(
                          $("<option></option>")
                          .attr("value", dataObj.id)
                          .text(dataObj.combined_period)
                      ); 
                  });
                }
                else // has errors
                  $( resSpan ).text(res.msg);
  
              } catch (error) { $(resSpan).text(err_msg); }
            },
  
            error: function (res) { $(resSpan).text(err_msg); }
  
          });
  
        }
    }

    $("#reportAuditUnit, #reportHOAuditUnit").on("change", function () {

        // function call
        get_assesment_data($(this));
      
    });
    
  }
});
