<?php 

//use Core\FormElements;

$authMarkup = '';
$authTableMarkup = '';
$srNo = 1;

foreach ($data['data']['circular_authority_data'] as $cAuthData):

    $authMarkup .= '<div class="col-md-6 col-lg-4 mb-4">' . "\n";
        $authMarkup .= '<div class="custom-dashboard-card h-100 bg-white border p-4">' . "\n";

            if(in_array($data['userDetails']['emp_type'], [6]))
                $url = $data['siteUrls']::getUrl('complianceCircularTaskSet') . '?auth=' . encrypt_ex_data($cAuthData -> id);
            else
                $url = $data['siteUrls']::getUrl( 'complianceProDashboard' ) . '/com-authority/' . encrypt_ex_data($cAuthData -> id);

            $authMarkup .= '<a href="'. $url .'" class="text-decoration-none">               
                <h6 class="title lead text-primary font-bold mb-1">'. htmlspecialchars($cAuthData -> name) .'</h6>
                <p class="font-sm text-secondary">Total Applicable Circulars: '. $cAuthData -> total_applicable_circulars .'</p>
            </a>' . "\n";

            $authMarkup .= '<div class="custom-list-info">
                <p>Total Pending Tasks</p>
                <p class="font-medium">'. $cAuthData -> total_tasks_pending .'</p>
            </div>' . "\n";

            $data['data']['total_data']['pending'] += $cAuthData -> total_tasks_pending;

            $authMarkup .= '<div class="custom-list-info">
                <p>Total Overdue Tasks</p>
                <p class="font-medium">'. $cAuthData -> total_tasks_overdue .'</p>
            </div>' . "\n";

            $data['data']['total_data']['overdue'] += $cAuthData -> total_tasks_overdue;

            $authMarkup .= '<div class="custom-list-info">
                <p>Total Penalty</p>
                <p class="font-medium">Rs. '. get_decimal($cAuthData -> total_penalty, 2) .'</p>
            </div>' . "\n";

            $authMarkup .= '<div class="custom-list-info">
                <p class="text-danger font-medium">Compliance Not Started</p>
                <p class="text-danger font-medium">'. $cAuthData -> total_not_started .'</p>
            </div>' . "\n";

        $authMarkup .= '</div>' . "\n";
    $authMarkup .= '</div>' . "\n";

    // table markup
    $authTableMarkup .= '<tr>' . "\n";

        // sr no
        $authTableMarkup .= '<td class="text-center">'. $srNo .'</td>' . "\n";

        // title
        $authTableMarkup .= '<td>';
            $authTableMarkup .= '<p class="font-medium text-primary mb-0">' . $cAuthData -> name . '</p>';
            $authTableMarkup .= '<p class="font-sm mb-0 text-secondary">Total Applicable Circulars: '. $cAuthData -> total_applicable_circulars .'</p>' . "\n";
        $authTableMarkup .= '</td>' . "\n";

        // counts
        $authTableMarkup .= '<td class="text-center">'. $cAuthData -> total_tasks_assign .'</td>' . "\n";
        $authTableMarkup .= '<td class="text-center">'. $cAuthData -> total_tasks_completed .'</td>' . "\n";
        $authTableMarkup .= '<td class="text-center">'. $cAuthData -> total_tasks_pending .'</td>' . "\n";
        $authTableMarkup .= '<td class="text-center">'. $cAuthData -> total_tasks_overdue .'</td>' . "\n";

        // action
        if( in_array($data['userDetails']['emp_type'], [3]) )
            $authTableMarkup .= '<td class="text-center">'. generate_link_button('link', ['href' => $url, 'extra' => view_tooltip('View') ]) .'</td>' . "\n";

    $authTableMarkup .= '</tr>' . "\n";

    $srNo++;

endforeach;

if (1):

    $markup = '<div class="row d-flex">' . "\n";

        $class = "col-md-6 col-lg-3";
        
        if(in_array($data['userDetails']['emp_type'], [6]))
            $metrics = [
                ['title' => 'Total Branches', 'img' => 'head-office.jpg', 'count' => $data['data']['total_data']['branches'] ],
                ['title' => 'Total Head Office', 'img' => 'branchs.jpg', 'count' => $data['data']['total_data']['ho'] ],
                ['title' => 'Pending Compliance [ Tasks ]', 'img' => 'cco-pending-compliance.png', 'count' => $data['data']['total_data']['pending'] ],
                ['title' => 'Total Overdue [ Task ]', 'img' => 'cco-overdue.png', 'count' => $data['data']['total_data']['overdue'] ]
            ];
        else
        {
            $class = "col-md-6 col-lg-4";

            $metrics = [
                ['title' => 'Total Circulars', 'img' => 'branchs.jpg', 'count' => $data['data']['total_data']['total_circular'] ],
                ['title' => 'Pending Compliance [ Tasks ]', 'img' => 'cco-pending-compliance.png', 'count' => $data['data']['total_data']['pending'] ],
                ['title' => 'Total Overdue [ Task ]', 'img' => 'cco-overdue.png', 'count' => $data['data']['total_data']['overdue'] ]
            ];
        }

        foreach ($metrics as $metric):
            $markup .= '<div class="'. $class .' mb-4">' . "\n";
                $markup .= '<div class="h-100 p-3 bg-white shadow-md border text-center">' . "\n";
                    $markup .= '<img class="img-fluid rounded-circle" src="'. IMAGES . $metric['img'] .'" alt="'. $metric['title'] .'" />' . "\n";
                    $markup .= '<div class="w-100 mb-3"></div>' . "\n";
                    $markup .= '<h4 class="font-bold font-lg mb-0" style="line-height:1">'. $metric['count'] .'</h4>' . "\n";
                    $markup .= '<p class="font-sm font-medium text-secondary mb-0">'. $metric['title'] .'</p>' . "\n";
                $markup .= '</div>' . "\n";
            $markup .= '</div>' . "\n";
        endforeach;
    $markup .= '</div>' . "\n";

    echo $markup;

endif; ?>

<h4 class="font-bold font-md mb-0">Authority Wise Short Report</h4>
<p class="font-sm text-secondary">Overview of Total Circulars, Pending Actions, Overdue Tasks, and Penalties.</p>

<div class="row d-flex">
    <?= $authMarkup ?>
</div>

<div class="bg-white border p-4">
    <div class="table-responsive">
    <table class="table table-hover v-table mb-0">
        <thead>
            <tr class="bg-light-gray border-top">
                <th class="text-center" width="60">Sr. No.</th>
                <th>Authority</th>
                <th class="text-center" width="160">Total [Tasks]</th>
                <th class="text-center" width="160">Total Compliance Completed [Tasks]</th>
                <th class="text-center" width="160">Total Pending Compliance [Tasks]</th>
                <th class="text-center" width="160">Total Overdue [Tasks]</th>
                <?php if(in_array($data['userDetails']['emp_type'], [3])): ?>
                <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>

        <?php echo $authTableMarkup; ?>

        </table>    
    </div>
</div>