<?php 

require_once 'single-period-markup.php';
require_once 'question-markup-common-code.php';

function generate_scheme_mrk($schemeData, $mrkType = 'scheme', $extra = [])
{
    $returnMrk = '';

    $i = 0;

    foreach($schemeData as $cId => $cData)
    {
        $i++;

        if($i == 1)
            $returnMrk .= '<tr>';

        $returnMrk .= '<td>';
            
        if($mrkType == 'scheme')
          $returnMrk .= '[ SCH. ' . $cData -> scheme_code . ' ] ' . string_operations($cData -> name, 'upper') . ' <span class="font-sm d-block text-secondary">[ '. string_operations( ('Mapped Category - ' . $cData -> cat_name), 'upper' ) .' ]</span>';

        elseif($mrkType == 'category')
          $returnMrk .= string_operations($cData -> name, 'upper') . (isset($extra['menu_name']) ? (' <span class="font-sm d-block text-secondary">[ '. string_operations( ('Mapped Menu - ' . $extra['menu_name']), 'upper' ) .' ]</span>') : '');  

        else
          $returnMrk .= string_operations($cData -> name, 'upper');            

        $returnMrk .= '</td>';        
    
        if($i == 2)  {
            $returnMrk .= '</tr>';
            $i = 0;
        }
    }

    if($i == 1)
        $returnMrk .= '<td></td></tr>';

    return $returnMrk;
}

if($data['data']['db_data'] -> section_type_id == 1):

?>

<div>
  <?php
    echo '<div class="mb-4"></div>' . "\n";
    
    // Original Update All button - updates multi_level_control_master for all branches
    echo generate_link_button('update', ['value' => 'Update All - Other Audit Unit Period Wise Questions', 'href' => $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-all-units/' . encrypt_ex_data($data['data']['db_data'] -> id), 'extra' => view_tooltip('Update All - Other Audit Unit Period Wise Questions')]);
    
    echo '<div class="mb-4"></div>' . "\n";
    
    // Radio buttons and button for updating audit_assesment_master
    echo '<div class="card border-primary mb-3">';
    echo '<div class="card-header bg-primary text-white">Update Branch Assessment</div>';
    echo '<div class="card-body">';
    
    // Form for updating audit_assesment_master
    echo '<form method="POST" action="' . $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-assessment/' . encrypt_ex_data($data['data']['db_data'] -> id) . '" id="assessmentUpdateForm">';
    
    echo '<div class="mb-3">';
    echo '<div class="form-check form-check-inline">';
    echo '<input class="form-check-input" type="radio" name="update_type" id="update_current" value="current" checked>';
    echo '<label class="form-check-label" for="update_current">Update Current Branch Assessment Only</label>';
    echo '</div>';
    echo '<div class="form-check form-check-inline">';
    echo '<input class="form-check-input" type="radio" name="update_type" id="update_all_branches" value="all">';
    echo '<label class="form-check-label" for="update_all_branches">Update All Branch Assessments</label>';
    echo '</div>';
    echo '</div>';
    
    echo '<button type="submit" class="btn btn-success" onclick="return confirmAssessmentUpdate();">';
    echo '<i class="fa fa-refresh"></i> Update Branch Assessment(s)';
    echo '</button>';
    
    echo '</form>';
    
    echo '</div>'; // end card-body
    echo '</div>'; // end card
    
    echo '<div class="mb-2"></div>' . "\n";
  ?>
</div>

<?php endif; ?>

<div class="card apcard rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Active Advances Schemes
  </div>
  <div class="card-body">
    <?php 
    
    echo generate_link_button('update', ['value' => 'Update Advances Scheme', 'href' => $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-scheme-advances/' . encrypt_ex_data($data['data']['db_data'] -> id), 'extra' => view_tooltip('Update Advances Scheme')]);
    echo '<div class="mb-2"></div>' . "\n";
    
    if( is_array($data['data']['db_data'] -> advances_scheme_data) && 
        sizeof($data['data']['db_data'] -> advances_scheme_data) > 0): ?>

        <h6 class="font-medium mb-2">Total Schemes: <?= sizeof($data['data']['db_data'] -> advances_scheme_data) ?></h6>

        <div class="height-400">
          <table class="table table-bordered v-table mb-0">
              <tr class="bg-light-gray"><th colspan="2">Scheme Name</th></tr>
              <?= generate_scheme_mrk($data['data']['db_data'] -> advances_scheme_data); ?>
          </table>
        </div>

    <?php else: 
        echo $data['noti']::getCustomAlertNoti('noDataFound');
    endif; ?>
  </div>
</div>

<div class="card apcard rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Active Deposit Schemes
  </div>
  <div class="card-body">
    <?php 
    
    echo generate_link_button('update', ['value' => 'Update Deposits Scheme', 'href' => $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-scheme-deposits/' . encrypt_ex_data($data['data']['db_data'] -> id), 'extra' => view_tooltip('Update Deposits Scheme')]);
    echo '<div class="mb-2"></div>' . "\n";
    
    if( is_array($data['data']['db_data'] -> deposits_scheme_data) && 
        sizeof($data['data']['db_data'] -> deposits_scheme_data) > 0): ?>

        <h6 class="font-medium mb-2">Total Schemes: <?= sizeof($data['data']['db_data'] -> deposits_scheme_data) ?></h6>

        <div class="height-400">
          <table class="table table-bordered v-table mb-0">
              <tr class="bg-light-gray"><th colspan="2">Scheme Name</th></tr>
              <?= generate_scheme_mrk($data['data']['db_data'] -> deposits_scheme_data); ?>
          </table>
        </div>

    <?php else: 
        echo $data['noti']::getCustomAlertNoti('noDataFound');
    endif; ?>
  </div>
</div>

<div class="card apcard rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Active Menus
  </div>
  <div class="card-body">
    <?php 
    
    echo generate_link_button('update', ['value' => 'Update Menus', 'href' => $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-menu/' . encrypt_ex_data($data['data']['db_data'] -> id), 'extra' => view_tooltip('Update Menus')]);
    echo '<div class="mb-2"></div>' . "\n";

    if( is_array($data['data']['db_menu_data']) && 
        sizeof($data['data']['db_menu_data']) > 0): ?>

        <h6 class="font-medium mb-2">Total Menus: <?= sizeof($data['data']['db_menu_data']) ?></h6>

        <div class="height-400">
          <table class="table table-bordered v-table mb-0">
              <tr class="bg-light-gray"><th colspan="2">Menu Name</th></tr>
              <?= generate_scheme_mrk($data['data']['db_menu_data'], 'menu'); ?>
          </table>
        </div>

    <?php else: 
        echo $data['noti']::getCustomAlertNoti('noDataFound');
    endif; ?>
  </div>
</div>

<div class="card apcard rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Active Categories
  </div>
  <div class="card-body">

    <?php

    echo generate_link_button('update', ['value' => 'Update Categories', 'href' => $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-category/' . encrypt_ex_data($data['data']['db_data'] -> id), 'extra' => view_tooltip('Update Categories')]);
    echo '<div class="mb-2"></div>' . "\n";

    $catCnt = 0;

    if( is_array($data['data']['db_category_data']) && 
        sizeof($data['data']['db_category_data']) > 0): 

        $tableMrk = '';
        
        foreach($data['data']['db_category_data'] as $cId => $cData)
        {
          if(isset($cData -> category_data))
          {
            $catCnt += sizeof($cData -> category_data);

            if(is_array($cData -> category_data) && sizeof($cData -> category_data) > 0)
            {
              $tableMrk .= generate_scheme_mrk($cData -> category_data, 'category', [ 'menu_name' => $cData -> name ]);
            }
          }
        }
    
    endif;

    if($catCnt > 0):

    ?>

      <h6 class="font-medium mb-2">Total Categories: <?= $catCnt ?></h6>

      <div class="height-400">
        <table class="table table-bordered v-table mb-0">
            <tr class="bg-light-gray"><th colspan="2">Category Name</th></tr>
            <?= $tableMrk; ?>
        </table>
      </div>

    <?php else: 
        echo $data['noti']::getCustomAlertNoti('noDataFound');
    endif; ?>
  </div>
</div>

<div class="card apcard rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Active Questions
  </div>
  <div class="card-body">
    <?php 
    
    echo generate_link_button('update', ['value' => 'Update Questions', 'href' => $data['siteUrls']::getUrl( 'multiLevelControlMaster' ) . '/update-questions/' . encrypt_ex_data($data['data']['db_data'] -> id), 'extra' => view_tooltip('Update Questions')]);
    echo '<div class="mb-2"></div>' . "\n";

    $formMarkup = question_checkbox_markup($data, 0);
    
    if($formMarkup['question_count'] > 0): 

      echo '<h6 class="font-medium mb-2">Total Questions: '. $formMarkup['question_count'] .'</h6>' . "\n";

      echo '<div class="height-400">';

        echo $formMarkup['markup'];
        
      echo '</div>';

    else:   
        echo $data['noti']::getCustomAlertNoti('noDataFound');
    endif; ?>
  </div>
</div>