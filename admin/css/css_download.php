<?php
        #$zip->ctrl_dir = 'man';

        # --- creates the page ---
        ob_start();

        include( 'core.php' );
        include( 'css_inc.php' );

        $content = ob_get_contents ();

        ob_end_clean();

        header("Cache-control: private");
        #header( "Content-type: application/octet-stream" );
        header( "Content-type: text/css" );
        header( "Content-Disposition: attachment; filename=\"filename.css\"" );
        header( "Content-Description: CSS File" );

        # --- ---
        echo $content;
?>
