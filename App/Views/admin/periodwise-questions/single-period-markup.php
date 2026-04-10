<div class="card apcard rounded-0">
  <div class="card-header pb-1 font-medium">
    Current Periodwise Set Details
  </div>
  <div class="card-body">
    <h5 class="card-title font-medium text-primary mb-1"><?= ($data['data']['audit_unit_data'][$data['data']['db_data'] -> audit_unit_id] -> name ?? ERROR_VARS['notFound'] ) ?></h5>
    <p class="mb-0 text-secondary font-sm">Period: <?= $data['data']['db_data'] -> start_month_year . ' - ' . $data['data']['db_data'] -> end_month_year ?> ( F.Y. <?= ($data['data']['year_data'][$data['data']['db_data'] -> year_id] -> fyear ?? ERROR_VARS['notFound'] ) ?> ), Created: <?= date($GLOBALS['dateSupportArray'][2], strtotime($data['data']['db_data'] -> created_at)) ?></p>
  </div>
</div>

<?php if(!isset($data['data']['remove_container'])): ?>
<div class='border bg-white p-3 mt-4'>
<?php endif; ?>