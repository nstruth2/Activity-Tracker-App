<?php
class Data {

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
     * Adds an item to the database
     */
    public function createData() {

        // Store the default status
        $server_results['status'] = 'success';
        $server_results['control'] = 'form';

        // Check the log-id field
        if(!isset($_POST['log-id'])) {
            $server_results['status'] = 'error';
            $server_results['message'] = 'Error: Missing log ID';
        } else {    
            $log_id = $_POST['log-id'];

            // Sanitize it to an integer
            $log_id = filter_var($log_id, FILTER_SANITIZE_NUMBER_FLOAT);
            if (!$log_id) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'Error: Invalid log ID';
            } else {

                // Check the activity-type field (required)
                if(!isset($_POST['activity-type'])) {
                    $server_results['status'] = 'error';
                    $server_results['message'] = 'Error: Missing activity type';
                } else {
                    $activity_type = $_POST['activity-type'];

                    // Sanitize it by accepting only one of three values: 'Walk', 'Run', or 'Cycle'
                    if ($activity_type !== 'Walk' AND $activity_type !== 'Run' AND $activity_type !== 'Cycle') {
                        $server_results['status'] = 'error';
                        $server_results['message'] = 'Error: Invalid activity type';
                    } else {

                        // Check the activity-date field (required)
                        if(!isset($_POST['activity-date'])) {
                            if(empty($activity_date)) {
                                $server_results['status'] = 'error';
                                $server_results['message'] = 'Error: Missing activity date';
                            } else {
                                $activity_date = $_POST['activity-date'];

                                // Check for a valid date (that is, one that uses the pattern YYYY-MM-DD)
                                if(!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[0-1])$/', $activity_date)) {
                                    $server_results['status'] = 'error';
                                    $server_results['message'] = 'Error: Invalid activity date';
                                }
                            }
                        }
                    }
                }
            }
        }
        // Check the activity-distance field
        $activity_distance = 0;
        if(isset($_POST['activity-distance'])) {
            $activity_distance = $_POST['activity-distance'];

            // Sanitize it to a floating-point value
            $activity_distance = filter_var($activity_distance, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        }
        // Check the activity-duration-hours field
        $activity_hours = 0;
        if(isset($_POST['activity-duration-hours'])) {
            $activity_hours = $_POST['activity-duration-hours'];
            $activity_hours = filter_var($activity_hours, FILTER_SANITIZE_NUMBER_FLOAT);
        }
        // Check the activity-duration-minutes field
        $activity_minutes = 0;
        if(isset($_POST['activity-duration-minutes'])) {
            $activity_minutes = $_POST['activity-duration-minutes'];
            $activity_minutes = filter_var($activity_minutes, FILTER_SANITIZE_NUMBER_FLOAT);
        }
        // Check the activity-duration-seconds field
        $activity_seconds = 0;
        if(isset($_POST['activity-duration-seconds'])) {
            $activity_seconds = $_POST['activity-duration-seconds'];
            $activity_seconds = filter_var($activity_seconds, FILTER_SANITIZE_NUMBER_FLOAT);
        }
        $activity_duration = $activity_hours . ':' . $activity_minutes . ':' . $activity_seconds;

        if($server_results['status'] === 'success') {

            // Create the SQL template
            $sql = "INSERT INTO activities
                           (log_id, type, date, distance, duration)
                    VALUES (?, ?, ?, ?, ?)";

            // Prepare the statement template
            $stmt = $this->_mysqli->prepare($sql);

            // Bind the parameters
            $stmt->bind_param("issds", $log_id, $activity_type, $activity_date, $activity_distance, $activity_duration);

            // Execute the prepared statement
            $stmt->execute();

            // Get the results
            $result = $stmt->get_result();

            if($this->_mysqli->errno === 0) {
                $server_results['message'] = 'Activity saved successfully! Sending you back to the activity log...';
            } else {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }
        }
        // Create and then output the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Returns all the items from the database
     */
    public function readAllData() {

        // Store the default status
        $server_results['status'] = 'success';

        // Check the log-id field
        if(!isset($_POST['log-id'])) {
            $server_results['status'] = 'error';
            $server_results['message'] = 'Error: Missing log ID';
        } else {
            $log_id = $_POST['log-id'];
            
            // Sanitize it to an integer
            $log_id = filter_var($log_id, FILTER_SANITIZE_NUMBER_FLOAT);
           if (!$log_id) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'Error: Invalid log ID';
            }
        }
        if($server_results['status'] === 'success') {

            // Create the SQL template
            $sql = "SELECT * FROM activities
                        WHERE log_id=?
                        ORDER BY date DESC";

            // Prepare the statement template
            $stmt = $this->_mysqli->prepare($sql);

            // Bind the parameter
            $stmt->bind_param("i", $log_id);

            // Execute the prepared statement
            $stmt->execute();

            // Get the results
            $result = $stmt->get_result();

            if($this->_mysqli->errno === 0) {
                // Get the query rows as an associative array
                $rows = $result->fetch_all(MYSQLI_ASSOC);

                // Convert the array to JSON, then output it
                $JSON_data = json_encode($rows, JSON_HEX_APOS | JSON_HEX_QUOT);
                return $JSON_data;
            } else {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }

        }
        if($server_results['status'] === 'error') {

            // Create and then output the JSON data
            $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
            return $JSON_data;
        }
    }
    /*
     * Returns a single item from the database
     */
    public function readDataItem() {

        // Store the default status
        $server_results['status'] = 'success';

        // Check the log-id field
        if(!isset($_POST['log-id'])) {
            $server_results['status'] = 'error';
            $server_results['message'] = 'Error: Missing log ID';
        } else {
            $log_id = $_POST['log-id'];

            // Sanitize it to an integer
            $log_id = filter_var($log_id, FILTER_SANITIZE_NUMBER_FLOAT);
            if (!$log_id) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'Error: Invalid log ID';
            } else {

                // Check the activity-id field
                if(!isset($_POST['activity-id'])) {
                    $server_results['status'] = 'error';
                    $server_results['message'] = 'Error: Missing activity ID';
                } else {
                    $activity_id = $_POST['activity-id'];

                    // Sanitize it to an integer
                    $activity_id = filter_var($activity_id, FILTER_SANITIZE_NUMBER_FLOAT);
                   if (!$activity_id) {
                        $server_results['status'] = 'error';
                        $server_results['message'] = 'Error: Invalid activity ID';
                    }
                }
            }
        }
        // Are we good?
        if($server_results['status'] === 'success') {

            // Create the SQL template
            $sql = "SELECT * FROM activities
                        WHERE log_id=?
                        AND activity_id=?
                        LIMIT 1";

            // Prepare the statement template
            $stmt = $this->_mysqli->prepare($sql);

            // Bind the parameters
            $stmt->bind_param("ii", $log_id, $activity_id);

            // Execute the prepared statement
            $stmt->execute();

            // Get the results
            $result = $stmt->get_result();

            if($this->_mysqli->errno === 0) {

                // Get the query row as an associative array
                $row = $result->fetch_all(MYSQLI_ASSOC);

                // Convert the array to JSON, then return it
                $JSON_data = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT);
                return $JSON_data;
            } else {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }

        }
        if($server_results['status'] === 'error') {

            // Create and then return the JSON string
            $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
            return $JSON_data;
        }
    }
    /*
     * Updates a data item with new values
     */
    public function updateData() {

        // Store the default status
        $server_results['status'] = 'success';

        // Check the log-id field
        if(!isset($_POST['log-id'])) {
            $server_results['status'] = 'error';
            $server_results['message'] = 'Error: Missing log ID';
        } else {    
            $log_id = $_POST['log-id'];

            // Sanitize it to an integer
            $log_id = filter_var($log_id, FILTER_SANITIZE_NUMBER_FLOAT);
            if (!$log_id) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'Error: Invalid log ID';
            } else {

                // Check the activity-type field (required)
                if(!isset($_POST['activity-type'])) {
                    $server_results['status'] = 'error';
                    $server_results['message'] = 'Error: Missing activity type';
                } else {
                    $activity_type = $_POST['activity-type'];

                    // Sanitize it by accepting only one of three values: 'Walk', 'Run', or 'Cycle'
                    if ($activity_type !== 'Walk' AND $activity_type !== 'Run' AND $activity_type !== 'Cycle') {
                        $server_results['status'] = 'error';
                        $server_results['message'] = 'Error: Invalid activity type';
                    } else {

                        // Check the activity-date field (required)
                        if(!isset($_POST['activity-date'])) {
                            if(empty($activity_date)) {
                                $server_results['status'] = 'error';
                                $server_results['message'] = 'Error: Missing activity date';
                            } else {
                                $activity_date = $_POST['activity-date'];

                                // Check for a valid date (that is, one that uses the pattern YYYY-MM-DD)
                                if(!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[0-1])$/', $activity_date)) {
                                    $server_results['status'] = 'error';
                                    $server_results['message'] = 'Error: Invalid activity date';
                                }
                            }
                        }
                    }
                }
            }
        }
        // Check the activity-distance field
        $activity_distance = 0;
        if(isset($_POST['activity-distance'])) {
            $activity_distance = $_POST['activity-distance'];

            // Sanitize it to a floating-point value
            $activity_distance = filter_var($activity_distance, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        // Check the activity-duration-hours field
        $activity_hours = 0;
        if(isset($_POST['activity-duration-hours'])) {
            $activity_hours = $_POST['activity-duration-hours'];
            $activity_hours = filter_var($activity_hours, FILTER_SANITIZE_NUMBER_FLOAT);
        }
        // Check the activity-duration-minutes field
        $activity_minutes = 0;
        if(isset($_POST['activity-duration-minutes'])) {
            $activity_minutes = $_POST['activity-duration-minutes'];
            $activity_minutes = filter_var($activity_minutes, FILTER_SANITIZE_NUMBER_FLOAT);
        }
        // Check the activity-duration-seconds field
        $activity_seconds = 0;
        if(isset($_POST['activity-duration-seconds'])) {
            $activity_seconds = $_POST['activity-duration-seconds'];
            $activity_seconds = filter_var($activity_seconds, FILTER_SANITIZE_NUMBER_FLOAT);
        }
        $activity_duration = $activity_hours . ':' . $activity_minutes . ':' . $activity_seconds;

        if($server_results['status'] === 'success') {

            // Create the SQL template
            $sql = "UPDATE activities
                    SET type=?, date=?, distance=?, duration=?
                    WHERE log_id=? AND activity_id=?";

            // Prepare the statement template
            $stmt = $this->_mysqli->prepare($sql);

            // Bind the parameters
            $stmt->bind_param("ssdsii", $activity_type, $activity_date, $activity_distance, $activity_duration, $log_id, $activity_id);

            // Execute the prepared statement
            $stmt->execute();

            // Get the results
            $result = $stmt->get_result();

            if($this->_mysqli->errno === 0) {
                $server_results['message'] = 'Activity updated successfully! Sending you back to the activity log...';
            } else {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }
        }
        // Create and then return the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
    /*
     * Removes an item from the database
     */
    public function deleteData() {

        // Store the default status
        $server_results['status'] = 'success';

        // Check the log-id field
        if(!isset($_POST['log-id'])) {
            $server_results['status'] = 'error';
            $server_results['message'] = 'Error: Missing log ID';
        } else {
            $log_id = $_POST['log-id'];
    
            // Sanitize it to an integer
            $log_id = filter_var($log_id, FILTER_SANITIZE_NUMBER_FLOAT);
            if (!$log_id) {
                $server_results['status'] = 'error';
                $server_results['message'] = 'Error: Invalid log ID';
            } else {
                // Check the activity-id field
                if(!isset($_POST['activity-id'])) {
                    $server_results['status'] = 'error';
                    $server_results['message'] = 'Error: Missing activity ID';
                } else {
                    $activity_id = $_POST['activity-id'];
    
                    // Sanitize it to an integer
                    $activity_id = filter_var($activity_id, FILTER_SANITIZE_NUMBER_FLOAT);
                   if (!$activity_id) {
                        $server_results['status'] = 'error';
                        $server_results['message'] = 'Error: Invalid actvity ID';
                    }
                }
            }
        }
        // Are we good?
        if($server_results['status'] === 'success') {

            // Create the SQL template
            $sql = "DELETE
                    FROM activities
                    WHERE log_id=?
                    AND activity_id=?";

            // Prepare the statement template
            $stmt = $this->_mysqli->prepare($sql);

            // Bind the parameters
            $stmt->bind_param("ii", $log_id, $activity_id);

            // Execute the prepared statement
            $stmt->execute();

            // Get the results
            $result = $stmt->get_result();

            if($this->_mysqli->errno === 0) {
                $server_results['message'] = 'Activity deleted successfully! Sending you back to the activity log...';
            } else {
                $server_results['status'] = 'error';
                $server_results['message'] = 'MySQLi error #: ' . $this->_mysqli->errno . ': ' . $this->_mysqli->error;
            }
        }
        // Create and then output the JSON data
        $JSON_data = json_encode($server_results, JSON_HEX_APOS | JSON_HEX_QUOT);
        return $JSON_data;
    }
}

?> 