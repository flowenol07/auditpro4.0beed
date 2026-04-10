<?php

namespace Core;

class Except {

    public static function exc_404($str = null, $toSelect = 0)
    {
        $data = [ 'me' => [ 'pageTitle' => 'Error Exception' ] ];
        $data['me'] = json_decode(json_encode($data['me']));

        require_once APP_ROOT . DS .  'Views/partials/head.php';

        /*-- content starts here --*/
        echo '<div class="container container-full-width pt-5">' . "\n";
            echo '<div class="row">' . "\n";
                echo '<div class="col-12 text-center py-4">' . "\n";
                    echo '<img class="img-fluid" src="'. IMAGES .'404-error.jpg" alt="404 Error" />' . "\n";

                    echo '<h4 class="font-md-2 font-medium text-danger">Oops.. You just found an error page..</h2>' . "\n";
                    
                    echo '<p class="lead font-weight-light">' . "\n";

                        if ($str != '') :
                            echo $str;
                        else :
                            echo 'Sorry! Cannot seem to find the page you were looking for...';
                        endif;

                    echo '</p>' . "\n";

                    if($toSelect)
                        echo '<a class="btn btn-primary icn-grid icn-home icn-bf" href="'. SiteUrls::getUrl('dashboard') . '/select-audit-unit/' .'">Back to Select Unit</a>' . "\n";
                    else
                        echo '<a class="btn btn-primary icn-grid icn-home icn-bf" href="'. SiteUrls::getUrl('dashboard') .'">Back to Dashboard</a>' . "\n";

                    echo '<a class="text-danger d-block" href="'. SiteUrls::getUrl('logout') .'">Logout &raquo;</a>';
                echo '</div>' . "\n";
            echo '</div>' . "\n";
        echo '</div>' . "\n";
        /*-- content ends here --*/       

        require_once APP_ROOT . DS . 'Views/partials/close.php';

    }

    public static function exc_access_restrict($str = null)
    {
        $data = [ 'me' => [ 'pageTitle' => 'Access Restricted' ] ];
        $data['me'] = json_decode(json_encode($data['me']));

        require_once APP_ROOT . DS .  'Views/partials/head.php';

        /*-- content starts here --*/
        echo '<div class="container container-full-width pt-5">' . "\n";
            echo '<div class="row">' . "\n";
                echo '<div class="col-12 text-center py-4">' . "\n";
                    echo '<img class="img-fluid" src="'. IMAGES .'access-restrict.jpg" alt="Access Restricted" />' . "\n";

                    echo '<h4 class="font-md-2 font-medium text-danger">Access Restricted</h2>' . "\n";
                    
                    echo '<p class="lead font-weight-light">' . "\n";

                        if ($str != '') :
                            echo $str;
                        else :
                            echo 'Access Restricted! You don\'t have permission to enter this page...';
                        endif;

                    echo '</p>' . "\n";

                    echo '<a class="btn btn-primary icn-grid icn-home icn-bf" href="'. SiteUrls::getUrl('dashboard') .'">Back to Dashboard</a>' . "\n";

                    echo '<a class="text-danger d-block" href="'. SiteUrls::getUrl('logout') .'">Logout &raquo;</a>' . "\n";
                echo '</div>' . "\n";
            echo '</div>' . "\n";
        echo '</div>' . "\n";
        /*-- content ends here --*/       

        require_once APP_ROOT . DS . 'Views/partials/close.php';

    }

}


?>