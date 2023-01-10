<?php
    include_once '../private/common/initialization.php';
    $page_title = 'Delete Your FootPower! Account?';
    include_once 'common/top.php';

    // Is the user signed in?
    if(isset($_SESSION['username'])):
?>
            <p>
                Whoa, are you sure you want to do this? You’ll lose all your data!
            <p>
            <form id="user-delete-form">
                <div class="form-wrapper">
                    <div class="control-wrapper">
                        <label for="password">Password</label>
                        <div>
                            <input id="password" class="form-control" name="password" type="password" minlength="8" aria-label="Type your password." required>
                            <br>
                            <input id="password-toggle" type="checkbox"><label for="password-toggle" class="label-horizontal">Show password</label>
                        </div>
                        <span id="password-error" class="error error-message"></span>
                    </div>
                    <button id="delete-user-button" class="btn btn-form" type="submit">Yep, I’m Sure</button>
                    <span id="form-error" class="error error-message form-error-message"></span>
                    <span id="form-message" class="form-message"></span>
                    <input type="hidden" id="username" name="username" value="<?php echo $_SESSION['username'] ?>">
                    <input type="hidden" id="user-verb" name="user-verb" value="delete-user">
                    <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token']; ?>">
                </div>
            </form>
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
