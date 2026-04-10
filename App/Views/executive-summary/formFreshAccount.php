<?php 
use Core\FormElements; 
require_once('generate_markup_branch_and_fresh.php');

$saveEnable = true;

?>

<?php echo FormElements::generateFormStart(["name" => "exe_summary_fresh_account", "action" => "", "id" => "fresh_accounts"]); ?>

<div class="card rounded-0 mb-4">
    <div class="card-header pb-1 font-medium text-uppercase">
    Number of New (Fresh) Accounts
    </div>
    <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">
                            Particulars
                        </th>
                        <th width="35%" class="text-center" colspan="2">
                            Position as on : <span class="text-primary d-inline-block"> <?= isset($this -> assesmentData -> assesment_period_from) ? $this -> assesmentData -> assesment_period_from : ''; ?></span> 
                        </th> 
                        <?php
                            if($data['userDetails']['emp_type'] == 4)
                            {
                                if($data['data']['assesmentData'] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'])
                                {
                        ?>
                                <th width="15%" class="text-center">
                                    Compliance Comment 
                                </th>
                                <th width="15%" class="text-center">
                                    Action 
                                </th>
                                <th width="15%" class="text-center">
                                    Reviewer Comment
                                </th>
                        <?php
                                }
                                else
                                {
                        ?>
                                <th width="20%" class="text-center">
                                    Action 
                                </th>
                                <th width="20%" class="text-center">
                                    Reviewer Comment
                                </th>

                        <?php
                                }
                            }
                            elseif($data['userDetails']['emp_type'] == 3)
                            {
                        ?>
                                <th width="20%" class="text-center">
                                    Compliance Comment
                                </th>
                        <?php
                            }
                        ?>                 
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($data['userDetails']['emp_type'] == 2)
                    {
                        $saveEnable = generate_markup_branch_and_fresh($data,2,0,1);
                    }
                    elseif($data['userDetails']['emp_type'] == 3)
                    {
                        $saveEnable = generate_markup_branch_and_fresh($data,3,0,1);
                    }
                    elseif($data['userDetails']['emp_type'] == 4)
                    {
                        generate_markup_branch_and_fresh($data,4,0,1);
                    }
                ?>
                </tbody>
            </table>
            <div class="box-footer text-center">
                <?php
                    if($data['userDetails']['emp_type'] == 2)
                    {
                        $reAudit = false;

                        foreach($this -> data['db_exe_fresh_account'] as $cId => $cDetails)
                        {
                            if($cDetails -> audit_status_id == 3)
                            {
                                $reAudit = true;
                            }
                        }
                        
                        if(($reAudit &&  $data['data']['assesmentData'] -> audit_status_id ==  3 ) || $data['data']['assesmentData'] -> audit_status_id == 1)
                        {
                            $btnArray = [ 'name' => 'insertFreshAccounts', 'value' => 'Save'];     

                            if($saveEnable)
                            {
                                if(!empty($this -> data['db_exe_fresh_account']))
                                {
                                    $btnArray['name'] = 'updateFreshAccounts';
                                    echo FormElements::generateSubmitButton('', $btnArray );
                                }
                                else
                                    echo FormElements::generateSubmitButton('', $btnArray );
                            }
                        }
                    }
                    elseif($data['userDetails']['emp_type'] == 3)
                    {
                        $reCompliance = false;

                        foreach($this -> data['db_exe_fresh_account'] as $cId => $cDetails)
                        {
                            if($cDetails -> compliance_status_id == 3)
                            {
                                $reCompliance = true;
                            }
                        }

                        if(($reCompliance &&  $data['data']['assesmentData'] -> audit_status_id == 6) || $data['data']['assesmentData'] -> audit_status_id == 4)
                        {
                            if($saveEnable)
                            {
                                if(!empty($this -> data['db_exe_fresh_account']))
                                {
                                    $btnArray = [ 'name' => 'updateFreshAccountsCompliance', 'value' => 'Save'];
                                    echo FormElements::generateSubmitButton('', $btnArray );
                                }
                            }
                        }
                    }
                    elseif($data['userDetails']['emp_type'] == 4)
                    {
                        
                        if(!empty($this -> data['db_exe_fresh_account']))
                        {
                            if($data['data']['assesmentData'] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'])
                            {
                                $btnArray = [ 'name' => 'updateFreshAccountsReviewCompliance', 'value' => 'Save'];
                                echo FormElements::generateSubmitButton('', $btnArray );
                            }
                            else
                            {
                                $btnArray = [ 'name' => 'updateFreshAccountsReviewAudit', 'value' => 'Save'];
                                echo FormElements::generateSubmitButton('', $btnArray );
                            }
                        }
                    }
                ?>
            </div>
    </div>
</div>

<?php echo FormElements::generateFormClose();  ?>