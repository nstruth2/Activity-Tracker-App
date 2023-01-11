<?php
class User {

    /* This private property stores the MySQLi
     * database object. A reference to the current
     * MySQLi object is passed to the constructor
     * when a data item object is created from the class.
     */
    private $_mysqli;

    /* When an object is created from this class,
     * use the following constructor to check that
     * the passed MySQLi object exists. If it does
     * not exist, create it.
     */
    public function __construct($mysqli=NULL) {

        // Does the $mysqli object exist?
        if (is_object($mysqli)) {

            // If so, store it in the class property
            $this->_mysqli = $mysqli;
        } else {

            // Otherwise, create the MySQLi object
            $this->_mysqli = new MySQLi(HOST, USER, PASSWORD, DATABASE);

            // Check for an error
            if($this->_mysqli->connect_error) {
                echo 'Connection Failed!
                      Error #' . $this->_mysqli->connect_errno
                        . ': ' . $this->_mysqli->connect_error;
                exit(0);
            }
        }
    }
    /*
     *==============*
     * User Methods *
     *==============*
     */
    /*
     * Adds a new user account to the database
     * and sends the user a verification email.
     */
    public function createUser() {

        // Store the default status
        $server_results['status'] = 'success';

        // Was the username sent?
        if(!isset($_POST['username'])) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'username';
            $server_results['message'] = 'Um, you really do need to enter your email address.';
        } else {

            // Sanitize it
            $username = $_POST['username'];
            $username = filter_var($username, FILTER_SANITIZE_EMAIL);
            if (!$username) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'username';
                $server_results['message'] = 'Hmmm. It looks like that email address isn\'t valid. Please try again.';
            } else {

                // Make sure the username doesn't already exist in the database
                $sql = "SELECT *
                        FROM users
                        WHERE username=?";
                $stmt = $this->_mysqli->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                // If the username already exists, num_rows will be greater than 0
                if ($result->num_rows > 0) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'username';
                    $server_results['message'] = 'Whoops! That email address is already being used. Please try again.';
                }
            }
        }

        // If all is still well, check the password
        if($server_results['status'] === 'success') {

            // Was the password sent?
            if(!isset($_POST['password'])) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'password';
                $server_results['message'] = 'That\'s weird: the password is missing. Please try again.';
            } else {

                // Sanitize it
                $password = $_POST['password'];
                $password = htmlspecialchars($password, ENT_QUOTES);

                // Is the password still valid?
                if (!$password) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'password';
                    $server_results['message'] = 'Sorry, but the password you used was invalid. Please try again.';
                }
                // Is the password long enough?
                elseif (strlen($password) < 8 ) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'password';
                    $server_results['message'] = 'Sorry, but the password must be at least 8 characters long. Please try again.';
                } else {

                    // If all's well, hash the password
                    $password = password_hash($password, PASSWORD_DEFAULT);
                }
            }
        }
        // If we're still good, it's time to add the user
        if($server_results['status'] === 'success') {

            // Create a random, secure, 32-character verification code
            $ver_code = bin2hex(openssl_random_pseudo_bytes(16));

            // Send the verification email
            $send_to = $username;
            $subject = 'Please verify your FootPower! account';
            $header = 'From: FootPower! <mail@mcfedries.com>' . "\r\n" .
                      'Content-Type: text/plain';
            $body = <<<BODY
You have a new account at FootPower!

Your username is the email address you provided: $username

Please activate your account by clicking the link below.

https://footpower.mcfedries.com/verify_user.php?vercode=$ver_code&username=$username

If you did not create a FootPower! account, you can safely delete this message.

Thanks!

Paul
footpower.mcfedries.com
BODY;

            $mail_sent = mail($send_to, $subject, $body, $header);

            if($mail_sent) {

                // Create and prepare the SQL template
                $sql = "INSERT INTO users
                               (username, password, verification_code)
                        VALUES (?, ?, ?)";
                $stmt = $this->_mysqli->prepare($sql);
                $stmt->bind_param("sss", $username, $password, $ver_code);
                $stmt->execute();
                $result = $stmt->get_result();

                if($this->_mysqli->errno === 0) {
                    $server_results['control'] = 'form';
                    $server_results['message'] = 'You\'re in! We\'ve sent you a verification email.<br>Be sure to click the link in that email to verify your account.';
                } else {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'form';
                    $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
                }
            } else {
                $server_results['status'] = 'error';
                $server_results['control'] = 'form';
                $server_results['message'] = 'Error! The verification email could not be sent, for some reason. Please try again.';
            }
        }

        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Verifies a new user account
     */
    public function verifyUser() {

        // Store the default status
        $server_results['status'] = 'success';

        // Get the query string parameters
        $ver_code = $_GET['vercode'];
        $username = $_GET['username'];

        // Sanitize them
        $ver_code = htmlspecialchars($ver_code, ENT_QUOTES);
        $username = filter_var($username, FILTER_SANITIZE_EMAIL);

        // Prepare the SQL SELECT statement
        $sql = "SELECT *
                FROM users
                WHERE verification_code=?
                AND username=?
                AND verified=0
                LIMIT 1";

        $stmt = $this->_mysqli->prepare($sql);
        $stmt->bind_param("ss", $ver_code, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Was there an error?
        if ($this->_mysqli->errno !== 0) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'form';
            $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
        }
        // Otherwise, if a row is returned, it means the user can be verified
        elseif ($result->num_rows === 1) {

            // Set the success message
            $server_results['message'] = 'Your account is now verified.<p>You\'re signed in, so go ahead and <a href="create_data.php">log a walk, run, or ride.</a>';

            // Sign in the user
            $_SESSION['username'] = $username;

            // Get the user's ID and distance unit
            $row = $result->fetch_all(MYSQLI_ASSOC);
            $user_id = $row[0]['user_id'];
            $distance_unit = $row[0]['distance_unit'];
            $_SESSION['distance_unit'] = $distance_unit;

            // Set the user's verified flag in the database
            $sql = "UPDATE users
                    SET verified=1
                    WHERE username=?";

            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Create a master data record (in this case, an activity log) for the user
            $sql = "INSERT INTO logs
                           (user_id)
                    VALUES (?)";
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Get the user's log ID
            $sql = "SELECT *
                    FROM logs
                    WHERE user_id=?
                    LIMIT 1";
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_all(MYSQLI_ASSOC);
            $log_id = $row[0]['log_id'];
            $_SESSION['log_id'] = $log_id;

        } else {
            // Handle the case where the user is already verified
            // Prepare the SQL SELECT statement
            $sql = "SELECT username
                    FROM users
                    WHERE verification_code=?
                    AND username=?
                    AND verified=1";
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("ss", $ver_code, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Was there an error?
            if($this->_mysqli->errno === 0) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'form';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }
            // Otherwise, if a row is returned, it means the user is already verified
            elseif ($result->num_rows > 0) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'form';
                $server_results['message'] = 'Yo, you\'re already verified.<p>Perhaps you\'d like to <a href="create_data.php">log a walk, run, or ride</a>?';
            } else {
                $server_results['status'] = 'error';
                $server_results['control'] = 'form';
                $server_results['message'] = 'Yikes. A database error occurred. These things happen.';
            }
        }
        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Signs a user into their account.
     */
    public function signInUser() {

        // Store the default status
        $server_results['status'] = 'success';

        // Was the username sent?
        if(!isset($_POST['username'])) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'username';
            $server_results['message'] = 'Doh! You need to enter your email address.';
        } else {

            // Sanitize it
            $username = $_POST['username'];
            $username = filter_var($username, FILTER_SANITIZE_EMAIL);
            if (!$username) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'username';
                $server_results['message'] = 'Well, it appears that email address isn\'t valid. Please try again.';
            } else {

                // Make sure the username exists in the database
                $sql = "SELECT *
                        FROM users
                        WHERE username=?
                        LIMIT 1";
                $stmt = $this->_mysqli->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                // If the username doesn't exist, num_rows will be 0
                if ($result->num_rows === 0) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'username';
                    $server_results['message'] = 'Sorry, but that email address isn’t associated with an account. Please try again.';
                } else {

                    // If all is still well, check the password
                    // Was the password sent?
                    if(!isset($_POST['password'])) {
                        $server_results['status'] = 'error';
                        $server_results['control'] = 'password';
                        $server_results['message'] = 'That\'s weird: the password is missing. Please try again.';
                    } else {

                        // Sanitize it
                        $password = $_POST['password'];
                        $password = htmlspecialchars($password, ENT_QUOTES);

                        // Is the password still valid?
                        if (!$password) {
                            $server_results['status'] = 'error';
                            $server_results['control'] = 'password';
                            $server_results['message'] = 'Sorry, but the password you used was invalid. Please try again.';
                        } else {

                            // Get the user data
                            $row = $result->fetch_all(MYSQLI_ASSOC);

                            // Confirm the password
                            if(!password_verify($password, $row[0]['password'])) {
                                $server_results['status'] = 'error';
                                $server_results['control'] = 'password';
                                $server_results['message'] = 'Sorry, but the password you used was incorrect. Please try again.';
                            } else {

                                // Sign in the user
                                $_SESSION['username'] = $username;
                                $user_id = $row[0]['user_id'];
                                $distance_unit = $row[0]['distance_unit'];
                                $_SESSION['distance_unit'] = $distance_unit;

                                // Get the user's log ID
                                $sql = "SELECT *
                                        FROM logs
                                        WHERE user_id=?";
                                $stmt = $this->_mysqli->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_all(MYSQLI_ASSOC);
                                $log_id = $row[0]['log_id'] ?? null;
                                $_SESSION['log_id'] = $log_id;
                            }
                        }
                    }
                }
            }
        }
        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Sends the user an email that includes
     * a link to reset the account password.
     */
    public function sendPasswordReset() {

        // Store the default status
        $server_results['status'] = 'success';

        // Was the email address entered?
        if(!isset($_POST['username'])) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'username';
            $server_results['message'] = 'Um, you really do need to enter your email address.';
        } else {

            // Sanitize it
            $username = $_POST['username'];
            $username = filter_var($username, FILTER_SANITIZE_EMAIL);
            if (!$username) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'username';
                $server_results['message'] = 'Hmmm. It looks like that email address isn\'t valid. Please try again.';
            } else {

                // Make sure the email address exists in the database
                $sql = "SELECT *
                        FROM users
                        WHERE username=?
                        LIMIT 1";
                $stmt = $this->_mysqli->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                // If the email doesn't exist, num_rows will be 0
                if ($result->num_rows === 0) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'username';
                    $server_results['message'] = 'Sorry, but that email address isn’t associated with an account. Please try again.';
                } else {

                    // Get the user's verification code
                    $row = $result->fetch_all(MYSQLI_ASSOC);
                    $ver_code = $row[0]['verification_code'];
                }
            }
        }
        // If we're still good, it's time to get the reset started
        if($server_results['status'] === 'success') {

            // Send the password reset email
            $send_to = $username;
            $subject = 'Reset your FootPower! password';
            $header = 'From: FootPower! <mail@mcfedries.com>' . "\r\n" .
                      'Content-Type: text/plain';
            $body = <<<BODY
You're receiving this message because you requested a password reset for your FootPower! account.

Please click the link below to reset your password.

https://footpower.mcfedries.com/reset_password.php?vercode=$ver_code&username=$username

If you do not have a FootPower! account, you can safely delete this message.

Thanks!

Paul
footpower.mcfedries.com
BODY;

            if(mail($send_to, $subject, $body, $header)) {

                // Unset the user's verified flag in the database
                $sql = "UPDATE users
                        SET verified=0
                        WHERE username=?";

                $stmt = $this->_mysqli->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();


                if($this->_mysqli->errno === 0) {
                    $server_results['control'] = 'form';
                    $server_results['message'] = 'Okay, we\'ve sent you the reset email.<br>Be sure to click the link in that email to reset your password.';
                } else {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'form';
                    $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
                }
            } else {
                $server_results['status'] = 'error';
                $server_results['control'] = 'form';
                $server_results['message'] = 'Error! The reset email could not be sent, for some reason. Please try again.';
            }
        }

        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Updates the user's password.
     */
    public function resetPassword() {

        // Store the default status
        $server_results['status'] = 'success';

        // Get the form data
        $username = $_POST['username'];
        $ver_code = $_POST['vercode'];
        $password = $_POST['password'];

        // Sanitize the username and verification code, just to be safe
        $username = filter_var($username, FILTER_SANITIZE_EMAIL);
        $ver_code = htmlspecialchars($ver_code, ENT_QUOTES); 

        // Verify the user:
        // First, prepare the SQL SELECT statement
        $sql = "SELECT *
                FROM users
                WHERE username=?
                AND verification_code=?
                AND verified=0";

        $stmt = $this->_mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $ver_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_all(MYSQLI_ASSOC);

        // Was there an error?
        if($this->_mysqli->errno !== 0) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'form';
            $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
        }
        // If a row is returned, it means the user is verified so the password can be reset
        elseif ($result->num_rows > 0) {

            // Was the password sent?
            if(!isset($password)) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'password';
                $server_results['message'] = 'That\'s weird: the password is missing. Please try again.';
            } else {

                // Sanitize it
                $password = htmlspecialchars($password, ENT_QUOTES);

                // Is the password still valid?
                if (!$password) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'password';
                    $server_results['message'] = 'Sorry, but the password you used was invalid. Please try again.';
                }
                // Is the password long enough?
                elseif (strlen($password) < 8 ) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'password';
                    $server_results['message'] = 'Sorry, but the password must be at least 8 characters long. Please try again.';
                }
                else {

                    // If all's well, hash the password
                    $password = password_hash($password, PASSWORD_DEFAULT);

                    // Set the distance unit session variable
                    $distance_unit = $row[0]['distance_unit'];
                    $_SESSION['distance_unit'] = $distance_unit;
                }
            }
        } else {
            $server_results['status'] = 'error';
            $server_results['control'] = 'form';
            $server_results['message'] = 'Oh, man, a database error occurred! Please try again. ';
        }

        // If we're still good, it's time to reset the password and re-verify the user
        if($server_results['status'] === 'success') {

            // Get the user's ID
            $user_id = $row[0]['user_id'];

            // Set the user's password and verified flag in the database
            $sql = "UPDATE users
                    SET password=?, verified=1
                    WHERE username=?";

            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("ss", $password, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Was there an error?
            if ($this->_mysqli->errno === 0) {

                // if not, sign in the user
                $_SESSION['username'] = $username;

                // Get the user's log ID
                $sql = "SELECT *
                        FROM logs
                        WHERE user_id=?";
                $stmt = $this->_mysqli->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                // Set the log_id and  variable
                $row = $result->fetch_all(MYSQLI_ASSOC);
                $log_id = $row[0]['log_id'];
                $_SESSION['log_id'] = $log_id;

            } else {
                $server_results['status'] = 'error';
                $server_results['control'] = 'form';
                $server_results['message'] = 'Yikes. A database error occurred. Please try again.';
            }
        }

        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Gets the user's distance unit.
     */
    public function getDistanceUnit() {

        // Store the default status
        $server_results['status'] = 'success';

        // Get the unit
        $server_results['message'] = $_SESSION['distance_unit'];

        // Create and then return the JSON string
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Updates the user's distance unit.
     */
    public function updateDistanceUnit() {

        // Store the default status
        $server_results['status'] = 'success';

        $username = $_SESSION['username'];
        $log_id = $_SESSION['log_id'];

        // Get the unit
        $distance_unit = $_POST['distance-unit'];

        // Validate it
        if(!isset($distance_unit)) {
            $server_results['status'] = 'error';
            $server_results['message'] = 'That\'s weird: the distance unit is missing. Please try again.';
        }
        elseif($distance_unit !== 'kilometers' && $distance_unit !== 'miles') {
                $server_results['status'] = 'error';
                $server_results['message'] = 'Invalid distance unit value. Please try again.';
        }
        else {

            // All's well, so update the user's unit
            $sql = "UPDATE users
                    SET distance_unit=?
                    WHERE username=?";
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("ss", $distance_unit, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Was there an error?
            if ($this->_mysqli->errno !== 0) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            } else {
                $server_results['message'] = 'Distance unit changed successfully!';
                $_SESSION['distance_unit'] = $distance_unit;
            }


            // Convert the user's distances to the new unit
            if ($distance_unit === 'miles') {
                $sql = "UPDATE activities
                        SET distance = distance / 1.609344
                        WHERE log_id=?";
            } else {
                $sql = "UPDATE activities
                        SET distance = distance * 1.609344
                        WHERE log_id=?";
            }
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->bind_param("i", $log_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Was there an error?
            if ($this->_mysqli->errno !== 0) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }
        }
        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Removes a user's account from the database.
     */
    public function deleteUser() {

        // Store the default status
        $server_results['status'] = 'success';

        // Get the username and password
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Sanitize the username, just to be safe
        $username = filter_var($username, FILTER_SANITIZE_EMAIL);

        // Make sure the username exists in the database
        $sql = "SELECT *
                FROM users
                WHERE username=?
                LIMIT 1";
        $stmt = $this->_mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Get the user's ID
        $row = $result->fetch_all(MYSQLI_ASSOC);
        $user_id = $row[0]['user_id'];

        // If the username doesn't exist, num_rows will be 0
        if ($result->num_rows === 0) {
            $server_results['status'] = 'error';
            $server_results['control'] = 'form';
            $server_results['message'] = 'Sorry, but we can\'t find your account. Please try again.';
        } else {

            // Now check the password
            // Was the password sent?
            if(!isset($_POST['password'])) {
                $server_results['status'] = 'error';
                $server_results['control'] = 'password';
                $server_results['message'] = 'That\'s weird: the password is missing. Please try again.';
            } else {

                // Sanitize it
                $password = htmlspecialchars($password, ENT_QUOTES);

                // Is the password still valid?
                if (!$password) {
                    $server_results['status'] = 'error';
                    $server_results['control'] = 'password';
                    $server_results['message'] = 'Sorry, but the password you used was invalid. Please try again.';
                } else {

                    // Confirm the password
                    if(!password_verify($password, $row[0]['password'])) {
                        $server_results['status'] = 'error';
                        $server_results['control'] = 'password';
                        $server_results['message'] = 'Sorry, but the password you used was incorrect. Please try again.';
                    } else {

                        // Delete the user
                        $sql = "DELETE
                                FROM users
                                WHERE username=?
                                LIMIT 1";
                        $stmt = $this->_mysqli->prepare($sql);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Was there an error?
                        if ($this->_mysqli->errno !== 0) {
                            $server_results['status'] = 'error';
                            $server_results['control'] = 'form';
                            $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
                        } else {

                             // Get the user's log ID
                            $sql = "SELECT *
                                    FROM logs
                                    WHERE user_id=?
                                    LIMIT 1";
                            $stmt = $this->_mysqli->prepare($sql);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_all(MYSQLI_ASSOC);
                            $log_id = $row[0]['log_id'] ?? null;

                           // Delete the user's activities
                            $sql = "DELETE
                                    FROM activities
                                    WHERE log_id=?";
                            $stmt = $this->_mysqli->prepare($sql);
                            $stmt->bind_param("i", $log_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            // Was there an error?
                            if ($this->_mysqli->errno !== 0) {
                                $server_results['status'] = 'error';
                                $server_results['control'] = 'form';
                                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
                            } else {
                                // Delete the user's master data record (log)
                                $sql = "DELETE
                                        FROM logs
                                        WHERE log_id=?
                                        LIMIT 1";
                                $stmt = $this->_mysqli->prepare($sql);
                                $stmt->bind_param("i", $log_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                // Was there an error?
                                if ($this->_mysqli->errno !== 0) {
                                    $server_results['status'] = 'error';
                                    $server_results['control'] = 'form';
                                    $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;

                                } else {
                                    // Free up all the session variables
                                    session_unset();
                                }
                            }
                        }
                    }
                }
            }
        }
        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
}
