
<div class="card apcard mb-4 rounded-0">
  <div class="card-header font-medium">
    Circular Details
  </div>
  
  <div class="card-body">
    <div class="table-responsive mb-2 show-hide-content-container" data-height="60" style="height: 60px; overflow: hidden;">
      <table class="table table-bordered v-table mb-0">

          <tr>
              <td colspan="4">
                  <p class="font-medium text-primary lead mb-0"><?= $data['data']['db_data'] -> name ?></p>

                  <?php if(!empty($data['data']['db_data'] -> description)): ?>
                    <p class="font-sm mb-2"><span class="font-medium">Circular Description: </span><?= !empty($data['data']['db_data'] -> description) ? $data['data']['db_data'] -> description : ''; ?></p>
                  <?php endif; ?>

                  <p class="text-secondary font-sm mb-0">Status: <?= check_active_status($data['data']['db_data'] -> is_active); ?>, Created: <?= date($GLOBALS['dateSupportArray'][2], strtotime($data['data']['db_data'] -> created_at)) ?></p>                  
              </td>
          </tr>

          <tr>
            <td class="font-medium">Circular No.</td>
            <td colspan="3"><?= !empty( $data['data']['db_data'] -> ref_no ) ?  $data['data']['db_data'] -> ref_no : '-' ?></td>
          </tr>

          <?php if(isset($data['data']['show_data'])): ?>
          <tr>
              <td class="font-medium">Authority</td>
              <td>
                <?= (isset($data['data']['circularAuthority']) && array_key_exists($data['data']['db_data'] -> authority_id, $data['data']['circularAuthority'])) ? $data['data']['circularAuthority'][ $data['data']['db_data'] -> authority_id ] -> name : ERROR_VARS['notFound']; ?>
              </td>

              <td class="font-medium">Circular Date</td>
              <td><?= $data['data']['db_data'] -> circular_date; ?></td>
          </tr>

          <tr>
              <td class="font-medium">Circular Type</td>
              <td><?= isset(COMPLIANCE_PRO_ARRAY['compliance_categories'][ $data['data']['db_data'] -> set_type_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_categories'][ $data['data']['db_data'] -> set_type_id ] : ERROR_VARS['notFound'] ?></td>

              <td class="font-medium">Priority:</td>
              <td class="font-medium text-danger"><?= isset(COMPLIANCE_PRO_ARRAY['compliance_priority'][ $data['data']['db_data'] -> priority_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_priority'][ $data['data']['db_data'] -> priority_id ] : ERROR_VARS['notFound'] ?></td>
          </tr>

          <tr>
              <td colspan="4">
                  <p class="font-medium text-danger mb-0">Penalty Amount: <?= get_decimal($data['data']['db_data'] -> penalty_amt, 2) ?></p>
                  <?php if(!empty($data['data']['db_data'] -> penalty_description)): ?>
                  <p class="font-sm mt-1 mb-0"><span class="font-medium">Penalty Description: </span><?= $data['data']['db_data'] -> penalty_description ?></p>
                  <?php endif; ?>
              </td>
          </tr>

          <?php /*<tr class="bg-light-gray">
              <td class="font-medium">Circular Frequency</td>
              <td class="font-medium" colspan="3"><?= isset(COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $data['data']['db_data'] -> frequency ]) ? COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $data['data']['db_data'] -> frequency ]['title'] : ERROR_VARS['notFound']; ?></td>
          </tr>
                    
          <?php
          
          $everyMonthStr = ' <span class="font-sm">(Every Month)</span>';

          if(in_array($data['data']['db_data'] -> frequency, [5,6]))
            $everyMonthStr = '';
          
          ?>

          <tr>
            <td class="font-medium">Reporting Date</td>
            <td><?= $data['data']['db_data'] -> reporting_date_1 . $everyMonthStr ?> </td>

            <td class="font-medium">Due Date <span class="font-sm">[For Branch / Department]</span></td>
            <td><?= $data['data']['db_data'] -> due_date_1 . $everyMonthStr ?></td>
          </tr>

          <?php if($data['data']['db_data'] -> frequency == 1) { ?>

            <tr>
              <td class="font-medium">Reporting Date 2 <span class="font-sm">[2nd Period]</span></td>
              <td><?= $data['data']['db_data'] -> reporting_date_2 . $everyMonthStr ?></td>

              <td class="font-medium">Due Date 2 <span class="font-sm">[For Branch / Department] [2nd Period]</span></td>
              <td><?= $data['data']['db_data'] -> due_date_2 . $everyMonthStr ?></td>
            </tr>

          <?php } */ ?>

          <?php endif; ?>

      </table>
    </div>

    <span class="show-hide-content">Show More &raquo;</span>

    <?php 
      
      if( 1/*isset($data['data']['cco_docs_true'])*/ )
      {
        
        $docsMrk = generate_circular_docs_markup($data['data']['db_data'], [ 'container' => 1, 'mt' => 1 ]);

        if(isset($data['data']['set_cco_docs_true']))
        {
          $extra = [ 'mt' => 1, 'circular_id' => $data['data']['db_data'] -> id ];

          if(empty($docsMrk))
            $extra['need_container'] = 1;

          if(isset($data['data']['cco_docs_true']))
            echo generate_compliance_doc_btn($extra, 1);
        }

        if(!empty($docsMrk))
          echo $docsMrk;
      }
    ?>
    
  </div>
</div>

<?php if(!isset($data['data']['remove_container'])): ?>
<div class='border bg-white p-4 mt-4'>
<?php endif; ?>