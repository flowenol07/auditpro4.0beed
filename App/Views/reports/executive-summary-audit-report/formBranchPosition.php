<?php 
require_once('generate_branch_and_fresh.php');
?>

<div class="card rounded-0 mb-4">
    <div class="card-header rmv-bottom-border pb-1 font-medium text-uppercase text-center">
        Branch Financial Position
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover v-table">
            <thead>
            <tr>
                <th style="width:5%"></th>
                <th style="width:15%">Particulars</th>
                <th style="width:20%" class="text-center">Last March Position (In Lakhs)</th>

                <th style="width:20%" class="text-center">
                    <span class="d-block">Position as on: </span><span class="text-primary d-block text-center"><?= $data['data']['assesmentData'] -> assesment_period_to ?? ''; ?></span> 
                </th>

                <th style="width:20%" class="text-center">
                    YTD Increase / Decrease <br>(In Lakhs)
                </th>

                <?php if($data['data']['report_type'] == 2) { ?>
                    <th style="width:20%" class="text-center">
                        Compliance Comment
                    </th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
                <?php           
                    generate_branch_and_fresh($data,1,0);
                ?>
            </tbody>
        </table>                  
    </div>
</div>