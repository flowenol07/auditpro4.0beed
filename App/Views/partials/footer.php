    </div>
  </div>
</div>
<!-- content ends here -->

<?php if(check_audit_remark_active_popup($data)): ?>
<div id="audit_remark_container" class="modal fade" data-url="<?= $data['siteUrls']::getUrl('assesmentRemarkMaster'); ?>/get-audit-remarks" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="audit_remark_containerLabel" aria-hidden="true">

  <div class="modal-dialog">
    <div class="modal-body bg-white">

    <div class="modal-content border-0 position-relative">

      <button type="button" class="btn-close rmk-modal-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>

      <div class="pe-5">
        <h4 class="font-bold mb-0">Assessment Remarks</h4>
        <p class="font-sm text-secondary mb-2">Assessment Period: <?= $data['db_assesment'] -> assesment_period_from . ' to ' . $data['db_assesment'] -> assesment_period_to . ' ( Frequency: '. $data['db_assesment'] -> frequency .' Month )'; ?></p>
      </div>

      <ul class="audit-rmk-nav mb-3">
        <li data-bs-target="#audit_remark_form_container">Add Remarks</li>
        <li data-bs-target="#audit_remark_current_container">Your Remarks</li>
        <li data-bs-target="#audit_remark_other_container">For You</li>
      </ul>

      <div class="border-bottom mb-3"></div>
      
      <div id="audit_remark_form_container" class="collapse audit-rmk-multi-container mb-3">
        <h6 class="font-medium mb-3"><u>Add New Remarks &raquo;</u></h6>
        <?php require_once APP_VIEWS . '/assesment-remarks/form.php'; ?>
      </div>

      <div id="audit_remark_current_container" class="collapse audit-rmk-multi-container mb-3">
        <h6 class="font-medium"><u>Your Remarks &raquo;</u></h6>
        <div class="rmk-res"></div>
      </div>

      <div id="audit_remark_other_container" class="collapse audit-rmk-multi-container mb-3">
        <h6 class="font-medium"><u>Other Remarks &raquo;</u></h6>
        <div class="rmk-res"></div>
      </div>
      
      </div>
      </div>
  </div>

</div>
<?php endif; ?>

<!-- footer starts here -->
<div id="footer" class="text-center p-3 pb-2 no-print">© Copyright <?= '2018 - ' .  date('Y')?>, <a class="" href="https://kredpool.com"> <strong class="font-bold">KredPool.com</strong></a> All Rights Reserved (Version 1.0)
</div>
<!-- footer ends here -->

<script src="<?= URL; ?>resources/js/jquery.min.js"></script>
<script src="<?= URL; ?>resources/js/bootstrap.bundle.min.js"></script>
<script>const reportName = "<?php echo $data['me'] -> pageHeading; ?>"</script>

<?php if(isset($data['data']['isCSVUpload'])): ?>
<script>
  const annex_csv_file_type = '<?= json_encode(array_values(FILE_UPLOADS_TYPES['csv'])); ?>';
  const annex_csv_file_size = <?= (FILE_UPLOADS_TYPES['csv_size']); ?>
</script>
<?php endif; ?>

<?php if(check_evidence_upload_strict()): ?>
<script>
  const evi_file_type = '<?= json_encode(array_values(EVIDENCE_UPLOAD['file_types'])); ?>';
  const evi_file_size = <?= (EVIDENCE_UPLOAD['size']); ?>
</script>
<?php endif; ?>

<?php if(isset($data['data']['cco_docs_true'])): ?>
<script>
  const cco_docs_type = '<?= json_encode(array_values(COMPLIANCE_PRO_ARRAY['compliance_docs_array']['file_types'])); ?>';
  const cco_docs_size = <?= (COMPLIANCE_PRO_ARRAY['compliance_docs_array']['size']); ?>
</script>
<?php endif; ?>

<?php if(isset($data['data']) && isset($data['data']['need_calender'])): ?>
  <script src="<?= URL; ?>resources/js/bootstrap-datepicker.min.js"></script>
<?php endif; ?>

<?php if(isset($data['data']) && isset($data['data']['need_select'])): ?>
  <script src="<?= URL; ?>resources/js/select2.min.js"></script>
<?php endif; ?>

<?php if(check_audit_remark_active_popup($data)): ?>
  <script src="<?= URL; ?>js/audit-remark-save-script.js"></script>
<?php endif; ?>

<?php if(isset($data['data']) && isset($data['data']['need_excel'])): ?>
  <script src="<?= URL; ?>js/tableToExcel.js"></script>

  <script>

  if($('#excelBtn').length > 0 && $('#exportToExcelTable, .exportToExcelTable').length > 0)
  {
    $('#excelBtn').on('click', function() {
      <?php if(isset($data['data']['fileName']) && isset($data['data']['need_excel'])): ?>
        table_excel_export('exportToExcelTable', '<?= $data['data']['fileName']; ?>');
      <?php else: ?>
        table_excel_export('exportToExcelTable', reportName);
      <?php endif; ?>
    });
  }

  </script>

<?php endif; ?>

<?php if(isset($data['data']) && isset($data['data']['need_datatable'])): ?>
<script src="<?= URL; ?>resources/js/datatables.min.js"></script>
<?php endif; ?>

<?php if(isset($data['data']) && isset($data['data']['need_print'])): ?>
<script>const pagePrint = "<?php echo isset($data['data']['page']) ? $data['data']['page'] : 'A4'; ?>"</script>
<script src="<?= URL; ?>js/print-preview.js"></script>
<?php endif; ?>

<script src="<?= URL; ?>resources/js/auditpro.min.js"></script>
<?php if( isset($data['data']) && isset($data['data']['need_dashboard'])): ?>
<script src="<?= URL; ?>resources/js/dashboard.js"></script>
<script src="<?= URL; ?>resources/js/canvasjs.min.js"></script>
<?php endif; ?>
