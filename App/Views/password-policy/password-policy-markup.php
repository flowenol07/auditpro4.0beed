<div id="pwd_policy_container" class="" data-regex="<?= $data['data']['db_data'] -> regex; ?>">
    <div class="">
        <h5 class="lead font-medium text-danger<?= isset($data['data']['markupAlign']) ? '' : ' text-center' ?> border px-3 py-2 mb-1">Password Policy &raquo;</h5>
        <ul class="custom_list_group<?= isset($data['data']['markupAlign']) ? '' : ' text-center' ?> mb-0">

        <li><span>Minimum password length: <b><?= $data['data']['db_data'] -> min_length ?></b></span></li>

        <?php if($data['data']['db_data'] -> num_cnt > 0): ?>
            <li data-regex="<?= $data['data']['db_data'] -> num_cnt_regex; ?>"><span>At least <b><?= $data['data']['db_data'] -> num_cnt ?></b> number (0-9)</span></li>
        <?php endif; ?>

        <?php if($data['data']['db_data'] -> lowercase_cnt > 0): ?>
            <li data-regex="<?= $data['data']['db_data'] -> lowercase_cnt_regex; ?>"><span>At least <b><?= $data['data']['db_data'] -> lowercase_cnt ?></b> lowercase letter (a-z)</span></li>
        <?php endif; ?>

        <?php if($data['data']['db_data'] -> uppercase_cnt > 0): ?>
            <li data-regex="<?= $data['data']['db_data'] -> uppercase_cnt_regex; ?>"><span>At least <b><?= $data['data']['db_data'] -> uppercase_cnt ?></b> uppercase letter (A-Z)</span></li>
        <?php endif; ?>

        <?php if($data['data']['db_data'] -> symbol_cnt > 0): ?>
            <li data-regex="<?= $data['data']['db_data'] -> symbol_cnt_regex; ?>"><span>At least <b><?= $data['data']['db_data'] -> symbol_cnt ?></b> special symbol (<?= $data['data']['allowedChars'] ?>)</span></li>
        <?php endif; ?>

        </ul>
    </div>  
</div>