<?php
    include_once '../private/common/initialization.php';
    $page_title = 'Add an Activity';
    include_once 'common/top.php';

    // Is the user signed in?
    if(isset($_SESSION['username'])):
        
        include_once 'common/data_form.php';
    else:
?>
            <p>
                You need to <a href="sign_up.php">sign up</a> for a FootPower! account to add an activity.
            </p>
            <p>
                Already have an account? Cool: Just <a href="sign_in.php">sign in</a> to add stuff.
            </p>
<?php
    endif;
    include_once 'common/sidebar.php';
    include_once 'common/bottom.php';
?>
