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
                                
                            <div class="text-center mb-3">
                                <img class="img-fluid border" src="<?= $data['userDetails']['emp_profile'] ?>" alt="<?= $data['userDetails']['emp_name'] ?>" width="80px" height="auto" />
                                <h4 class="lead mt-3 mb-1">Hello! <?= $data['userDetails']['emp_name']; ?></h4>
                                <p class="text-danger mb-2">Password policy changed! Please change your password...</p>
                            </div>

                            <?= $data['noti']::getSessionAlertNoti(); ?>
                            
                            <?php if(is_object($data['data']['db_data'])): ?>

                            <form class="mb-3" method="post" action="<?= URL . $data['me'] -> url . ''; ?>">

                                <div class="form-group mb-3">
                                    <input type="password" id="password" class="form-control" name="password" placeholder="Password">
                                    <?= $data['noti']::getInputNoti($data['request'], 'password_err'); ?>
                                </div>

                                <div class="form-group mb-3">
                                    <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder="Confirm Password">
                                    <?= $data['noti']::getInputNoti($data['request'], 'confirm_password_err'); ?>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary icn-grid icn-update icn-bf">Change Password</button>
                                    <a class="btn btn-outline-secondary icn-grid" href="<?= $data['siteUrls']::getUrl('logout') ?>">Back to Login &raquo;</a>
                                </div>
                            </form>

                            <?php require_once APP_VIEWS . DS . 'password-policy/password-policy-markup.php'; ?>

                            <?php else: ?>

                                <?= $data['noti']::getCustomAlertNoti('passwordPolicyNotFound'); ?>
                                
                            <?php endif; ?>

                        </div>    
                    </div>

                    <div class="text-center text-secondary p-3 mt-2 mb-4">© Copyright 2018 - 2024: <a class="text-primary" href="https://kredpool.com"> <strong class="font-bold">KredPool.com</strong></a> 
                        <span class="font-sm d-block">( Version 1.0 )</sapn>
                    </div>

                </div>	
            </div>

        </div>

    </div>
</div>