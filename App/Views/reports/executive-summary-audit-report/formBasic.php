<div class="card rounded-0 mb-4">
    <div class="card-header rmv-bottom-border pb-1 font-medium text-uppercase text-center">
        Current Financial Year : <span class="text-primary font-bold"><?= $data['data']['db_year_data'][$data['data']['assesmentData'] -> year_id] ?? '' ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 table-responsive">                    
                <table class="table table-hover v-table">
                    <tbody>
                        <tr>
                            <td style="width:10%" class="text-center">1</td>
                            <td style="width:50%">Branch Name</td>
                            <td style="width:40%"><span id="branch_name_exe_summary"><?= $data['data']['audit_unit_data'][ $data['data']['assesmentData'] -> audit_unit_id ] -> name ?? '' ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">2</td>
                            <td style="width:50%">Inspection Period</td>
                            <td style="width:40%"><span id="inspection_period"><?= $data['data']['assesmentData'] -> frequency ?? 0 ?></span> Months</td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">3</td>
                            <td style="width:50%">Name of Branch Manager </td>
                            <td style="width:40%"><span id="branch_manager_name"><?= $data['data']['employee_data'][$data['data']['assesmentData'] -> branch_head_id] -> combined_name ?? '' ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">4</td>
                            <td style="width:50%">Name of Assistant Branch Manager</td>
                            <td style="width:40%"><span id="assistant_branch_manager_name"><?= $data['data']['employee_data'][$data['data']['assesmentData'] -> branch_subhead_id] -> combined_name ?? '' ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">5</td>
                            <td style="width:50%">Inspection Conducted by</td>
                            <td style="width:40%"><span id="inspection_conducted_by"><?= $data['data']['employee_data'][$data['data']['assesmentData'] -> audit_emp_id] -> combined_name ?? '' ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">6</td>
                            <td style="width:50%">Inspection Start Date</td>
                            <td style="width:40%"><span id="inspection_start_date"><?= $data['data']['assesmentData'] -> audit_start_date ?? '' ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">7</td>
                            <td style="width:50%">Inspection End Date</td>
                            <td>
                                <span id="inspection_end_date"><?= $data['data']['assesmentData'] -> audit_end_date ?? '' ?></span>
                            </td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">8</td>
                            <td style="width:50%">Number of Days taken for Inspection</td>
                            <td style="width:40%">
                                <span id="no_of_days_taken_for_inspection">
                                    <?php 
                                        
                                        if( isset($data['data']['assesmentData'] -> audit_end_date) && 
                                            $data['data']['assesmentData'] -> audit_end_date != "" )
                                        {
                                            $startDate = date_create($data['data']['assesmentData'] -> audit_start_date);
                                        
                                            $endDate = date_create($data['data']['assesmentData'] -> audit_end_date);

                                            $diff = date_diff($startDate, $endDate);

                                            echo  $diff-> format("%r%a Days");
                                        }
                                        else
                                            echo 'Audit Not Completed';

                                    ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">9</td>
                            <td style="width:50%">Audit Report Submitted Date</td>
                            <td style="width:40%"><?= $data['data']['exeBasicData'] -> report_submitted_date ?? '' ?></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">10</td>
                            <td style="width:50%">Compliance to be done before date</td>
                            <td style="width:40%"><span id="compliance_due_date"><?= isset($data['data']['assesmentData'] -> compliance_due_date) ? $data['data']['assesmentData'] -> compliance_due_date : date('Y-m-d', strtotime($data['data']['assesmentData'] -> audit_start_date. ' + 15 days')) ; ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">11</td>
                            <td style="width:50%">Compliance done date</td>
                            <td style="width:40%"><span id="compliance_end_date"><?= $data['data']['assesmentData'] -> compliance_end_date ?? '' ; ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">12</td>
                            <td style="width:50%">Number of Staff including Contractual / Daily wages staff</td>
                            <td style="width:40%" id="staff_count"><?= $data['data']['exeBasicData'] -> staff_count ?? 0 ?></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">13</td>
                            <td style="width:50%">Approximate Number of manual Challans per day</td>
                            <td style="width:40%" id="staff_count"><?= $data['data']['exeBasicData'] ->manual_challans_per_day ?? 0 ?></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">14</td>
                            <td style="width:50%">CD Ratio</td>
                            <td style="width:40%"><span id="cd_ratio">0</span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">15</td>
                            <td style="width:50%">Per Employee Business (In Lakhs)</td>
                            <td style="width:40%"><span id="per_emp_business">0</span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">16</td>
                            <td style="width:50%">Annual Incremental Deposit Target (IN LAKHS)</td>
                            <td style="width:40%"><span id="deposit_target"><?= get_decimal(($data['data']['db_target'][0] -> deposit_target ?? 0), 2) ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">17</td>
                            <td style="width:50%">Annual Incremental Advances Target  (IN LAKHS)</td>
                            <td style="width:40%"><span id="advances_target"><?= get_decimal(($data['data']['db_target'][0] -> advances_target ?? 0), 2) ?></span></td>
                        </tr>

                        <tr>
                            <td style="width:10%" class="text-center">18</td>
                            <td style="width:50%">Annual Differential NPA Target (IN LAKHS)</td>
                            <td style="width:40%"><span id="npa_target"><?= get_decimal(($data['data']['db_target'][0] -> npa_target ?? 0), 2) ?></span></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>