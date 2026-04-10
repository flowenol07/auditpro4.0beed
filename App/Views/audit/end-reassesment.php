<?php 

use Core\FormElements; 
require_once 'audit-common-code.php';

$pendingCnt = 0;
$complianceCnt = 0;
$colspan = 6;

// display assesment details
echo generate_assesment_top_markup($data['data']['db_assesment_data']);

// ans data
if(isset($data['data']['reaudit_ans']) && $data['data']['reaudit_ans'] > 0)
    $complianceCnt += $data['data']['reaudit_ans'];

// annex data
if(isset($data['data']['reaudit_annex']) && $data['data']['reaudit_annex'] > 0)
    $complianceCnt += $data['data']['reaudit_annex'];

if( is_array($data['data']['db_menu']) && sizeof($data['data']['db_menu']) > 0 &&
    is_array($data['data']['db_category']) && sizeof($data['data']['db_category']) > 0 &&
    is_array($data['data']['db_sets']) && sizeof($data['data']['db_sets']) > 0 )
{
    // HAS QUESTIONS DATA
    $markup = '<table class="table table-bordered mb-0 v-table">';

    $cfCheck = check_carry_forward_strict();

    foreach( $data['data']['db_menu'] as $cMenuId => $cMenuDetails ) {

        $markup .= '<tr class="menu-tr">
            <th colspan="'. $colspan .'" class="text-primary"><u>'. string_operations(('Menu: ' . $cMenuDetails -> name), 'upper') .'</u></th>
        </tr>';

        if($cfCheck && CARRY_FORWARD_ARRAY['id'] == $cMenuId)
        {
            // function call
            $cfFuncCall = audit_end_asses_generate_cf_markup($data, $data['data']['db_assesment_data'], ['pending' => true]);
            $complianceCnt += $cfFuncCall['cnt'];
            $pendingCnt += $cfFuncCall['pending'];

            // FOR CF ANSWER // function call
            $markup .= '<td colspan="'. $colspan .'">';
                $markup .= $cfFuncCall['markup'];
            $markup .= '</td>';
        }
        else
        {
            // FOR OTHER QUESTIONS
            foreach( $cMenuDetails -> categories as $cCatId => $cCatDetails ) {
                
                $cCatSets = !empty($cCatDetails -> question_set_ids) ? explode(',', $cCatDetails -> question_set_ids) : [];

                $DUMPCATEGORY = 0;

                if( array_key_exists($cCatDetails -> linked_table_id, $GLOBALS['schemeTypesArray']) )
                    $DUMPCATEGORY = $cCatDetails -> linked_table_id;

                if( $DUMPCATEGORY == 0 || ($DUMPCATEGORY != 0 && isset($data['data']['db_category'][ $cCatId ] -> schemes)) ) {

                    $randStr = audit_end_asses_generate_rand_str();

                    // current category
                    $cCategoryTr = '<tr class="category-tr">
                        <td colspan="'. $colspan .'" class="font-medium category-container-head"><u>'. string_operations(('Category: ' . $cCatDetails -> name), 'upper') .'</u></td>
                    </tr>';

                    $markup .= $cCategoryTr;

                    if(is_array($cCatSets) && sizeof($cCatSets) > 0) {

                        // $loopCnt = 0;
                        $accMarkupArray = [];
                        
                        if($DUMPCATEGORY != 0 ) {

                            foreach($data['data']['db_category'][ $cCatId ] -> schemes as $cSchemeId => $cAccCnt) {

                                foreach($data['data'][ 'db_scheme_data_' . $DUMPCATEGORY ][ $cSchemeId ] -> accounts as $cAccId ) {

                                    $cArr = [ 'markup' => '', 'acc' => $data['data'][ 'db_dump_data_'  . $DUMPCATEGORY ][ $cAccId ] ];

                                    $cArr['markup'] = generate_account_markup_for_report($data, $data['data']['db_assesment_data'], $cArr['acc'], ['hideData' => 1, 'needAcClass' => 1]);

                                    $accMarkupArray[] = $cArr;
                                }
                            }
                        }
                        
                        $cSetLoop = $DUMPCATEGORY != 0 ? sizeof($accMarkupArray) : 1;

                        for($i = 0; $i < $cSetLoop; $i++) {

                            $cAccDetails = null;

                            // display account details
                            if( $DUMPCATEGORY != 0 )
                            {
                                $markup .= $accMarkupArray[ $i ]['markup'];
                                $cAccDetails = $accMarkupArray[ $i ]['acc'];
                            }

                            $cMarkup = '';

                            // has data
                            foreach($cCatSets as $cSetId):

                                if( array_key_exists($cSetId, $data['data']['db_sets']) && 
                                    is_object($data['data']['db_sets'][ $cSetId ]) > 0 && 
                                    isset($data['data']['db_sets'][ $cSetId ] -> headers) &&
                                    sizeof($data['data']['db_sets'][ $cSetId ] -> headers ) > 0 )
                                {    
                                    // function call
                                    $responseData = audit_end_asses_generate_set_markup(
                                        $colspan, 
                                        $randStr, 
                                        $cCatDetails -> id, 
                                        $data['data']['db_sets'][ $cSetId ], 
                                        $data, $cAccDetails
                                    );

                                    if(!is_object($cAccDetails))
                                        $markup .= $responseData['markup'];
                                    else
                                        $cMarkup = $responseData['markup'];

                                    // increament pending counter
                                    if($responseData['pending'] != null) $pendingCnt += $responseData['pending'];
                                }

                            endforeach;

                            // reassign 
                            $markup .= $cMarkup;

                            // re assign category name for account
                            if( $i != ($cSetLoop - 1) ) {
                                
                                $markup .= '<tr class="'. (is_object($cAccDetails) ? ('acc-' . $cAccDetails -> id) : '') .'"><td colspan="'. $colspan .'" class="bg-light">&nbsp;</td></tr>';
                                $markup .= $cCategoryTr;
                            }

                        }
                    }
                    else
                        $markup .= '<tr>
                            <td colspan="'. $colspan .'">'. $data['noti']::getNoti('noDataFound') .'</td>
                        </tr>';

                }
            }
        }
    }

    $markup .= '</table>';

    $esCheckArray = generate_executive_summary_markup($data, 1);

    if( $esCheckArray['count'] > 0 ) $pendingCnt += $esCheckArray['count']; ?>

    <p class="text-danger mb-2">Pending Re Assesment Points: <span id="pendingPoints" class="font-medium"><?= $pendingCnt ?></span></p>
    <?= $data['noti']::cError('<b>Note </b> - Total Re Assesment Compliance Points: <span id="compliancePoints" class="font-medium">'. $complianceCnt .'</span>', 'warning'); ?>

    <?php if( !empty($esCheckArray['str']) ) echo $esCheckArray['str']; ?>

    <div class="card apcard mb-4">
        <div class="card-header">
                End Assesment 
            </div>

            <div class="card-body">
                <?= $markup; ?>
            </div>
        </div>

<?php 
}
else
{
    // NO QUESTIONS DATA FOUND // CHECK FOR EXECUTIVE SUMMARY
    $esCheckArray = generate_executive_summary_markup($data);

    // check pending count
    if( $esCheckArray['pending_count'] > 0 ) $pendingCnt += $esCheckArray['pending_count'];

?>

    <div class="card apcard mb-4">
        <div class="card-header">
            <?php echo ( $esCheckArray['count'] > 0 ) ? string_operations('Executive Summary', 'upper') : string_operations('No data found!', 'upper'); ?>
        </div>
        <div class="card-body">

            <?php if( !$esCheckArray['count'] > 0 ):
                echo $data['noti']::getCustomAlertNoti('noDataFound'); 
            else:     
                echo $esCheckArray['str'];
            endif; ?>

        </div>
    </div>

<?php 

// else close
}

if( check_re_assesment_status($data['data']['db_assesment_data']) && 
    $data['data']['exe_summary_data']['pending_reaudit'] > 0 )
    $pendingCnt += $data['data']['exe_summary_data']['pending_reaudit'];

if( !($pendingCnt > 0) ) {

    // function call
    end_assesment_form_markup_generate($data, [
        'compliance_cnt' => $complianceCnt,
        'pending_cnt' => $pendingCnt,
    ]);

} 

?>