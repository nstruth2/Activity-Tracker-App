<?php
    include_once '../private/common/initialization.php';

    // Is the user already signed in?
    if(isset($_SESSION['username'])) {
        $page_title = 'You’re Already Verified';
    } else {
        $page_title = 'Welcome to FootPower!';
        // Initialize the results
        $server_results['status'] = 'success';
        $server_results['control'] = '';
        $server_results['message'] = '';
        
        // Make sure a verification code was passed
        if (!isset($_GET['vercode'])) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'form';
            $server_results['message'] = 'Error: Invalid URL. Sorry it didn\'t work out.';
        }
        // Make sure the username was passed
        elseif (!isset($_GET['username'])) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'form';
            $server_results['message'] = 'Error: Invalid user.';
        }
        // If we get this far, all is well, so go for it
        else {
    
            // Include the User class
            include_once '../private/classes/user_class.php';
    
            // Create a new User object
            $user = new User($mysqli);
            
            // Verify the new account
            $server_results = json_decode($user->verifyUser(), TRUE);
        }
    }

    include_once 'common/top.php';
    
    // Was the user already verified?
    if($page_title === 'You’re Already Verified'):
?>
            <section>
                <p>
                    You already have an account, so nothing to see here.
                </p>
                <p>
                    Did you want to <a href="create_data.php">log an activity</a>, instead?
                </p>
                <p>
                    Or perhaps you want to <a href="sign_out.php">sign out</a>?
                </p>
            </section>
<?php
    else:
?>
            <div class="result-message"><?php echo $server_results['message'] ?></div>
<?php    
    endif;
    include_once 'common/sidebar.php';
    include_once 'common/bottom.php';
?>