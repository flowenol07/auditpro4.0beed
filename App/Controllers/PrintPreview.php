<?php

namespace Controllers;

use Core\Controller;

class PrintPreview extends Controller {

    public $me = null;

    public function __construct($me) {

        $this -> me = $me;

    }

    public function index() {

        $content = null;

        $pagesArray = [
            'A4'  => 'A4',
            'A4L' => 'A4 landscape',
            'A5' => 'A5',
            'A5L' => 'A5 landscape'
        ];

        if(isset($_POST['printPage']) && isset($pagesArray[ $_POST['printPage'] ]))
            $page = $pagesArray[ $_POST['printPage'] ];
        else
            $page = $pagesArray['A4'];

        if (isset($_POST['content']))
            $content = $_POST['content'];
        else
        {
            echo "No content to print.";
            exit;
        }

        if(!empty($content))
        {
            $content = '
            <div id="header">
                <div id="auditproLogo" style="background-image:url(' . URL . 'resources/img/auditpro-logo.png)"></div>
                <div id="reportData">
                    <p class="font-sm"><span class="font-medium">Report Date: '. date('d-m-Y') .'</p>
                    <h4>Bank: '. BANK_NAME .'</h4>
                </div>
                <div class="clearfix"></div>
            </div>

            <div>'. $content .'</div>';

            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Print Report</title>
                <style>
                    @page { size: '. $page .'; }
                </style>
                <link href="'. URL .'resources/css/print.min.css" rel="stylesheet"/>
            </head>
            <body>
                <div id="printArea">'. $content .'</div>
                <script>
                    window.PagedConfig = {
                        after: (flow) => { window.print(); }
                    };
                </script>
                <script src="'. URL .'js/paged.polyfill.js"></script>
            </body>
            </html>' . "\n";
        }
        else
        {
            echo "No content to print.";
            exit;
        }
    }
}

?>