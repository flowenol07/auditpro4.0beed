
<div class="">
    <div class="postion-relative">
            
        <div id="auditproInfoContainer" style="background-image:url(<?= URL; ?>images/footer-bg.jpg) !important;">

            <div class="text-center mb-2">
                <img class="" src="<?= ASSETS_IMG; ?>auditpro-logo.png" alt="AuditPro Logo" width="180px" />
                <p class="text-white font-medium text-uppercase mb-3">Risk Based Internal Audit</p>
            </div>

        </div>

        <div id="auditproLoginContainer" class="container" >
                
                <div class="row ">

                    <div class="col-md-10 col-lg-5 mx-auto">

                        <div class="card rounded-0 p-4 shadow">
                            <div class="card-body">
                                <h5 class="card-title text-center font-md font-medium mb-1"><?= $data['me'] -> pageHeading; ?></h5>
                                <p class="card-text text-center font-medium text-primary"><?= BANK_NAME; ?></p>
                                <hr class="text-secondary" />

                                <p class="text-secondary mb-2">Login to start your session!</p>

                                <?= $data['noti']::getSessionAlertNoti(); ?>

                                <form method="post" action="<?= $data['siteUrls']::getUrl('auth') . '/login'; ?>">

                                    <div class="form-group mb-2">
                                        <label class="form-label font-medium d-block text-dark mb-1" for="emp_code">Employee Code</label>
                                        <input id="emp_code" type="text" name="emp_code" class="form-control" value="<?= $data['request'] -> input('emp_code', ''); ?>" placeholder="Enter Employee Code" />
                                        <?= $data['noti']::getInputNoti($data['request'], 'emp_code_err'); ?>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label font-medium d-block text-dark mb-1" for="password">Password</label>

                                        <div id="passwordContainer">
                                            <input id="password" type="password" name="password" class="form-control" placeholder="Enter Password" />
                                            <span id="showHidePassword" class="hide-pass-text"></span>
                                        </div>
                                        <?= $data['noti']::getInputNoti($data['request'], 'password_err'); ?>
                                    </div>

                                    <button type="submit" class="btn btn-primary d-block w-100 font-medium">Login</button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center text-secondary p-3 mt-2 mb-4">© Copyright <?= '2018 - ' .  date('Y')?>: <a class="text-primary" href="https://kredpool.com"> <strong class="font-bold">KredPool.com</strong></a> 
                            <span class="font-sm d-block">( Version 1.0 )</sapn>
                        </div>

                    </div>

                </div>
                
            </div>

    </div>	
</div>

<?php

$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function(){$("#showHidePassword").click(function(){$(this).toggleClass("show-pass-text");var t=$("#password");"password"==t.attr("type")?t.attr("type","text"):t.attr("type","password")})});
</script>';

?>