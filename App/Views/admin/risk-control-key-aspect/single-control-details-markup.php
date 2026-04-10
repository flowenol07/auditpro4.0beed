<div class="card rounded-0">
  <div class="card-header pb-1 font-medium">
    Current Control Risk Type Details
  </div>
  <div class="card-body">
    <h5 class="card-title font-medium text-primary mb-1"><?= $data['data']['db_control'] -> name ?></h5>
    <p class="mb-0 text-secondary font-sm">Status: <?= check_active_status($data['data']['db_control'] -> is_active); ?>, Created: <?= date($GLOBALS['dateSupportArray'][2], strtotime($data['data']['db_control'] -> created_at)) ?></p>
  </div>
</div>

<div class='border bg-white p-4 mt-4'>