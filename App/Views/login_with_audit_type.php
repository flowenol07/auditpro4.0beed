<div class="">
    <div class="position-relative">

        <!-- Info / Banner -->
        <div id="auditproInfoContainer" style="background-image:url(<?= URL; ?>images/footer-bg.jpg) !important;">
            <div class="text-center mb-2">
                <img src="<?= ASSETS_IMG; ?>auditpro-logo.png" alt="AuditPro Logo" width="180px" />
                <p class="text-white font-medium text-uppercase mb-3">Risk Based Internal Audit</p>
            </div>
        </div>

        <!-- Login Form Container -->
        <div id="auditproLoginContainer" class="container">
            <div class="row">
                <div class="col-md-10 col-lg-5 mx-auto">

                    <div class="card rounded-0 p-4 shadow">
                        <div class="card-body">

                            <!-- Page Heading -->
                            <h5 class="card-title text-center font-md font-medium mb-1">
                                <?= $data['me']->pageHeading; ?>
                            </h5>

                            <!-- Bank Name -->
                            <p class="card-text text-center font-medium text-primary">
                                <?= BANK_NAME; ?>
                            </p>

                            <hr class="text-secondary" />

                            <p class="text-secondary mb-3 text-center">
                                Please select your Audit Type to continue
                            </p>

                            <!-- Session Alerts -->
                            <?= $data['noti']::getSessionAlertNoti(); ?>

                            <!-- Branch Selection Form -->
                            <form method="post" action="<?= $data['siteUrls']::getUrl('auth') . '/login_with_audit_type'; ?>">

                                <!-- Branch Dropdown -->
                                <div class="form-group mb-3">
                                    <label class="form-label font-medium d-block text-dark mb-1">
                                        Select Audit Type
                                    </label>

                                    <select name="audit-type" class="form-control">
                                        <option value="">-- Select Audit Type --</option>
                                        <?php foreach (IS_MULTI_AUDIT['branches'] as $key => $branch): ?>
                                            <option value="<?= $key ?>" 
                                                <?= ($data['request']->input('audit-type') == $key || ($_SESSION['audit-type'] ?? '') == $key) ? 'selected' : ''; ?>>
                                                <?= $branch['label'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Branch Error Notification -->
                                    <?= $data['noti']::getInputNoti($data['request'], 'branch_err'); ?>
                                </div>

                                <!-- Continue Button -->
                                <button type="submit" class="btn btn-primary d-block w-100 font-medium">
                                    Continue
                                </button>

                            </form>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center text-secondary p-3 mt-2 mb-4">
                        © Copyright <?= '2018 - ' . date('Y') ?>:
                        <a class="text-primary" href="https://kredpool.com">
                            <strong class="font-bold">KredPool.com</strong>
                        </a>
                        <span class="font-sm d-block">( Version 1.0 )</span>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
