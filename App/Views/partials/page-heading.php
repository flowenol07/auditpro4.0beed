<div class="container-fluid main-container no-print">
    <div class="row">
    <div class="<?= ((isset($data['data']['topBtnArr'])) ? 'col-md-9' : 'col-md-12') ?> mb-2 mb-md-0">
            <h3 class="font-bold font-md-2 mb-0"><?= $data['me'] -> pageHeading; ?></h3>

            <?= generate_breadcrumb($data['siteUrls'], $data['me']); ?>
        </div>

        <?php if(isset($data['data']['topBtnArr'])): ?>
        <div class="col-md-3 d-flex align-items-center justify-content-md-end page-top-btns">
            <?= generate_page_btn_array($data['data']['topBtnArr']); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="container-fluid mb-4" style="padding: 0 1.1em">
    
<?php if(!isset($data['data']['data_container'])): ?>
    <div class="border bg-white p-4<?= isset($data['data']['need_print']) ? ' print_container' : ''; ?>">
<?php endif; ?>