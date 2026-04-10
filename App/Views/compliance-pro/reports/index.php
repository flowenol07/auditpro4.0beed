<div class="table-responsive">
    <table class="table table-hover kp-table v-table border bg-white">
        <thead>
            <tr>
                <th class="text-center">Sr. No.</th>
                <th>Reports</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $srNo = 1;

                foreach($data['data']['reports'] as $cReportType => $cReportTypeDetails )
                {
                    echo '<tr class="bg-light-gray">
                            <td class="text-center text-primary font-bold font-sm">#</td>
                            <td colspan="2" class="text-primary font-medium">'. $cReportTypeDetails['title'] .' &raquo;</td>
                        </tr>' . "\n";

                    foreach( $cReportTypeDetails['reports'] as $cReport )
                    {
                        $me = $data['siteUrls']::get($cReport);

                        echo'
                        <tr>
                            <td align="center">' . $srNo . '</td>
                            <td>' . $me -> pageTitle .'</td>
                            <td align="center">';

                            echo generate_link_button('link', ['href' => $me -> url, 'extra' => 'target = "_blank"']);

                        echo '</td>
                        </tr>';

                        $srNo++;
                    }
                }
            ?>
        </tbody>
    </table>
</div>