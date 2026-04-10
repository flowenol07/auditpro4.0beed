<?php 
use Core\FormElements;

echo FormElements::generateFormStart(["name" => "exe_summary_basic_details", "action" => "", "id" => "basic_details"]); 
?>

<div class="card apcard mb-4">
    <div class="card-header text-center">
        Current Financial Year : <span class="text-light font-bold"><?= isset($data['assesmentData'] -> year_details -> year) ? $data['assesmentData'] -> year_details -> year : ''; ?> - <?= isset($data['assesmentData'] -> year_details -> year) ? $data['assesmentData'] -> year_details -> year + 1 : '' ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 table-responsive">
                    
                <table class="table table-hover v-table">
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Branch Name</td>
                        <td>
                            <span id="branch_name_exe_summary"><?= isset($data['assesmentData'] -> audit_unit_details -> name) ? $data['assesmentData'] -> audit_unit_details -> name : ''; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Inspection Period</td>
                        <td>
                            <span id="inspection_period"><?= isset($data['assesmentData'] -> frequency) ? $data['assesmentData'] -> frequency : 0; ?></span> Months
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Name of Branch Manager </td>
                        <td>
                            <span id="branch_manager_name"><?= isset($data['assesmentData'] -> branch_head_details -> name) ? $data['assesmentData'] -> branch_head_details -> name : ERROR_VARS['notAvailable']; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Name of Assistant Branch Manager</td>
                        <td>
                            <span id="assistant_branch_manager_name"><?= isset($data['assesmentData'] -> branch_subhead_details -> name) ? $data['assesmentData'] -> branch_subhead_details -> name : ERROR_VARS['notAvailable']; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Inspection Conducted by</td>
                        <td><span id="inspection_conducted_by"><?= isset($data['assesmentData'] -> audit_head_details -> name) ? $data['assesmentData'] -> audit_head_details -> name : ERROR_VARS['notAvailable']; ?></span></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Inspection Start Date</td>
                        <td>
                            <span id="inspection_start_date"><?= isset($data['assesmentData'] -> audit_start_date) ? $data['assesmentData'] -> audit_start_date : ''; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Inspection End Date</td>
                        <td>
                            <span id="inspection_end_date"><?= isset($data['assesmentData'] -> audit_end_date) ? $data['assesmentData'] -> audit_end_date : '' ; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Number of Days taken for Inspection</td>
                        <td>
                            <span id="no_of_days_taken_for_inspection">
                                <?php 
                                    
                                    if(isset($data['assesmentData'] -> audit_end_date) && $data['assesmentData'] -> audit_end_date != "" )
                                    {
                                        $startDate = date_create($data['assesmentData'] -> audit_start_date);
                                    
                                        $endDate = date_create($data['assesmentData'] -> audit_end_date);

                                        $diff = date_diff($startDate, $endDate);

                                        $totalDays = $diff->format("%r%a") + 1;

                                        echo $totalDays . " Days";
                                    }
                                    else
                                    {
                                        echo 'Audit Not Completed';
                                    }
                                    
                                ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Audit Report Submitted Date</td>
                        <td>
                            <?php
                                $markup = FormElements::generateInput([
                                    "id" => "report_submitted_date", "name" => "report_submitted_date", "appendClass" => "date_cls", 
                                    "type" => "text", "value" => $data['request'] -> input('report_submitted_date', $data['data']['db_exe_basic_details'] -> report_submitted_date), 
                                    "placeholder" => "Report Submitted Date",
                                    "disabled" => ($data['userDetails']['emp_type'] != 2)
                                ]);
            
                                echo FormElements::generateFormGroup($markup, $data, 'report_submitted_date');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>Compliance to be done before date</td>
                        <td><span id="compliance_due_date"><?= isset($data['assesmentData'] -> compliance_due_date) ? $data['assesmentData'] -> compliance_due_date : date('Y-m-d', strtotime($data['assesmentData'] -> audit_start_date. ' + 15 days')) ; ?></span></td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td>Compliance done date</td>
                        <td><span id="compliance_end_date"><?= isset($data['assesmentData'] -> compliance_end_date) ? $data['assesmentData'] -> compliance_end_date : '' ; ?></span></td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td>Number of Staff including Contractual/Daily wages staff</td>
                        <td>
                            <?php
                                $markup = FormElements::generateInput([
                                    "id" => "staff_count", "name" => "staff_count",
                                    "type" => "text", "value" => $data['request'] -> input('staff_count', $data['data']['db_exe_basic_details'] -> staff_count), 
                                    "placeholder" => "Number of Staffs",
                                    "disabled" => ($data['userDetails']['emp_type'] != 2)
                                ]);
            
                                echo FormElements::generateFormGroup($markup, $data, 'staff_count');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>13</td>
                        <td>Approximate Number of manual Challans per day</td>
                        <td>
                            <?php
                                $markup = FormElements::generateInput([
                                    "id" => "manual_challans_per_day", "name" => "manual_challans_per_day", "value" => $data['request'] -> input('manual_challans_per_day', $data['data']['db_exe_basic_details'] -> manual_challans_per_day), 
                                    "type" => "text", 
                                    "placeholder" => "Challans per day",
                                    "disabled" => ($data['userDetails']['emp_type'] != 2)
                                ]);
            
                                echo FormElements::generateFormGroup($markup, $data, 'manual_challans_per_day');
                            ?>
                    </tr>
                    <tr>
                        <td>14</td>
                        <td>CD Ratio</td>
                        <td><span id="cd_ratio">0</span></td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td>Per Employee Business (In Lakhs)</td>
                        <td><span id="per_emp_business">0</span></td>
                    </tr>
                    <tr>
                        <td>16</td>
                        <td>Annual Incremental Deposit Target (IN LAKHS)</td>
                        <td><span id="deposit_target"><?php echo isset($data['data']['db_target'][0] -> deposit_target) ? $data['data']['db_target'][0] -> deposit_target : 0 ?></span></td>
                    </tr>
                    <tr>
                        <td>17</td>
                        <td>Annual Incremental Advances Target  (IN LAKHS)</td>
                        <td><span id="advances_target"><?php echo isset($data['data']['db_target'][0] -> advances_target) ? $data['data']['db_target'][0] -> advances_target : 0 ?></span></td>
                    </tr>
                    <tr>
                        <td>18</td>
                        <td>Annual Differential NPA Target (IN LAKHS)</td>
                        <td><span id="npa_target"><?php echo isset($data['data']['db_target'][0] -> npa_target) ? $data['data']['db_target'][0] -> npa_target : 0 ?></span></td>
                    </tr>

                </tbody>
            </table>
            </div>
        </div>

        <div class="box-footer text-center">
        <?php
        if((isset($data['data']['assesmentData'] -> audit_status_id) && $data['data']['assesmentData'] -> audit_status_id != 3 && $data['data']['assesmentData'] -> audit_status_id == 1))
        {
            if($data['userDetails']['emp_type'] == 2)
            {
                $btnArray = [ 'name' => 'insertBasicDetails', 'value' => 'Save'];     

                if($data['data']['btn_type_basic'] == 'update')
                {
                    $btnArray['name'] = 'updateBasicDetails';
                    echo FormElements::generateSubmitButton('', $btnArray );
                }
                else
                    echo FormElements::generateSubmitButton('', $btnArray );
            }
        }
        ?>        
        </div>
    </div>
</div>

<?php echo FormElements::generateFormClose();  ?>