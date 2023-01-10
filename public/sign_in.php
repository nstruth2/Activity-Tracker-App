<?php
    include_once '../private/common/initialization.php';
    
    // Set the page title depending on whether the user is signed in
    if(isset($_SESSION['username'])) {
        $page_title = 'You’re Signed In to Your Account';
    } else {
        $page_title = 'Sign In to Your Account';
    }
    include_once 'common/top.php';
    
    // Is the user already signed in?
    if(isset($_SESSION['username'])):
?>
            <section>
                <p>
                    You’re already signed in, so nothing to see here.
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
            <form id="user-sign-in-form">
                <div class="form-wrapper">
                    <div class="control-wrapper">
                        <label for="username">Email</label>
                        <input id="username" class="form-control" name="username" type="email" aria-label="Type your email address." required />
                        <span id="username-error" class="error error-message"></span>
                    </div>
                    <div class="control-wrapper">
                        <label for="password">Password</label>
                        <div>
                            <input id="password" class="form-control" name="password" type="password" minlength="8" aria-label="Type your password." required>
                            <br>
                            <input id="password-toggle" type="checkbox"><label for="password-toggle" class="label-horizontal">Show password</label>
                        </div>
                        <span id="password-error" class="error error-message"></span>
                    </div>
                    <button id="sign-me-in-button" class="btn btn-form" type="submit">Sign Me In</button>
                    <span id="form-error" class="error error-message form-error-message"></span>
                    <span id="form-message" class="form-message"></span>
                    <input type="hidden" id="user-verb" name="user-verb" value="sign-in-user">
                    <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token']; ?>">
                </div>
            </form>
            <div>
                <a href="request_new_password.php">Forgot your password?</a>
            </div>
<?php
    endif;
    include_once 'common/sidebar.php';
    include_once 'common/bottom.php';
?>
