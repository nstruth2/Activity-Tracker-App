<?php
    include_once '../private/common/initialization.php';
    $page_title = 'Edit Activity';
    include_once 'common/top.php';

    // Is the user signed in?
    if(isset($_SESSION['username'])):
        
        include_once 'common/data_form.php';
?>
            <!-- The jQuery UI dialog markup for Delete This Activity-->
            <div id="confirm-delete" class="activity-delete-dialog" title="Delete This Activity?" role="dialog">
                	<p>Are you sure you want to remove this activity from your log? This action can’t be undone!</p>
            </div>

<?php
    else:
?>
            <p>
                You need to <a href="sign_up.php">sign up</a> for a FootPower! account to edit an activity.
            </p>
            <p>
                Already have an account? Sweet! Just <a href="sign_in.php">sign in</a> to edit stuff.
            </p>
<?php
    endif;
    include_once 'common/sidebar.php';
    include_once 'common/bottom.php';
?>
