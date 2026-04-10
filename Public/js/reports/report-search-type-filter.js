$(document).ready(function () {

  if( $('#selectSearchTypeFilter').length > 0 && 
      $('.show_hide_container').length > 0 )
  {
    function check_show_hide_container(containerArr)
    {
      $.each(containerArr, function(i, cArr) {
        if( $('#' + cArr).length > 0 )
          $('#' + cArr).removeClass('d-none');
      });
    }

    function show_hide_container(selectedVal, onChangeBool = 1)
    {
      $('.show_hide_container').addClass('d-none');

      if(onChangeBool && $('#reportAuditAssesment').length > 0)
        $('#reportAuditAssesment option:not(:first-child)').remove();

      if(onChangeBool && selectedVal != '3' && $('#selectBranchContainer').length > 0)
        $('#selectBranchContainer').val('');

      if(onChangeBool && selectedVal != '2' && $('#selectHOContainer').length > 0)
        $('#selectHOContainer').val('');

      if(selectedVal.length > 0)
      {
        switch (selectedVal) {
          
          case '1':
          case '2':
            check_show_hide_container(['dateFilterContainer', 'rmvPendingContainer']);
            break;

          case '3':
            check_show_hide_container(['selectBranchContainer', 'selectAssessmentContainer']);
            break;

          case '4':
            check_show_hide_container(['selectHOContainer', 'selectAssessmentContainer']);
            break;

          case '5':
            check_show_hide_container(['selectBranchContainer', 'dateFilterContainer', 'rmvPendingContainer']);
            break;

          case '6':
            check_show_hide_container(['selectHOContainer', 'dateFilterContainer', 'rmvPendingContainer']);
            break;
        
          default:
            $('.show_hide_container').addClass('d-none');
            break;
        }
      }
    }

    $('#selectSearchTypeFilter').on('change', function() {

      // function call
      show_hide_container( $(this).val() );

    });

    show_hide_container( $('#selectSearchTypeFilter').val(), 0 );
  }
  
});
