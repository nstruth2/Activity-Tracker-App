<?php
    include_once '../private/common/initialization.php';
    $page_title = 'Your FootPower! Account';
    include_once 'common/top.php';

    // Is the user signed in?
    if(isset($_SESSION['username'])):
        
        // Get the user's distance unit
        if($_SESSION['distance_unit'] === 'miles') {
            $m_checked = 'checked';
            $k_checked = '';
        } else {
            $k_checked = 'checked';
            $m_checked = '';
        }
?>
            <p>
                <strong>Your FootPower! username:</strong><br>
                <?php echo $_SESSION['username'] ?>
            </p>
            <p>
                Choose your preferred distance unit:
            <p>
            <form id="update-distance-unit-form">
                <fieldset>
                    <legend>
                        Select a Distance Unit
                    </legend>
                    <div class="control-wrapper">
                        <input id="distance-unit-kilometers" type="radio" name="distance-unit" value="kilometers" aria-label="Radio button kilometers" <?php echo $k_checked ?>>
                        <label class="label-horizontal" for="distance-unit-kilometers">Kilometers</label> 
                        <input id="distance-unit-miles" type="radio" name="distance-unit" value="miles" aria-label="Radio button miles" <?php echo $m_checked ?>>
                        <label class="label-horizontal" for="distance-unit-miles">Miles</label> 
                        <span id="form-error" class="error error-message form-error-message"></span>
                        <span id="form-message" class="form-message"></span>
                    </div>
                </fieldset>
                <input type="hidden" id="username" name="username" value="<?php echo $_SESSION['username'] ?>">
                <input type="hidden" id="user-verb" name="user-verb" value="update-unit">
                <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token']; ?>">
            </form>
            <p>
                Here are the tasks you can perform with your account:
            <p>
                <a href="request_new_password.php">Change Your Password</a>
            </p>
            <p>
                <a href="sign_out.php">Sign Out</a>
            </p>
            <p>
                <a href="delete_account.php">Delete Your Account</a>
            </p>
<?php
    else:
?>
        <!-- Display the sign-in page -->
        <meta http-equiv="refresh" content="0;sign_in.php">
<?php
    endif;
    include_once 'common/sidebar.php';
    include_once 'common/bottom.php';
?>
