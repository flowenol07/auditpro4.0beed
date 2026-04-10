<!DOCTYPE html>
<html lang="en">
<head>  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>AuditPro - <?= $data['me'] -> pageTitle; ?></title>
  <link rel="icon" type="image/x-icon" href="<?= URL; ?>favicon.ico">	
  
  <link rel="stylesheet" type="text/css" href="<?= URL; ?>resources/css/bootstrap.min.css">

  <?php if(isset($data['data']) && isset($data['data']['need_datatable'])): ?>
  <link href="<?= URL; ?>resources/css/datatables.min.css" rel="stylesheet"/>
  <?php endif; ?>

  <?php if(isset($data['data']) && isset($data['data']['need_calender'])): ?>
  <link href="<?= URL; ?>resources/css/bootstrap-datepicker3.min.css" rel="stylesheet"/>
  <?php endif; ?>

  <?php if(isset($data['data']) && isset($data['data']['need_select'])): ?>
  <link href="<?= URL; ?>resources/css/select2.min.css" rel="stylesheet"/>
  <?php endif; ?>

  <link rel="stylesheet" type="text/css" media="screen" href="<?= URL; ?>resources/css/auditpro.min.css" />
  
  <?php if(isset($data['data']) && isset($data['data']['need_dashboard'])): ?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?= URL; ?>resources/css/dashboard.css" />
  <?php endif; ?>

  <?php $bodyClass = ""; ?>

</head>
<body<?= !empty($bodyClass) ? ' class="'. $bodyClass .'"' : '' ?>>