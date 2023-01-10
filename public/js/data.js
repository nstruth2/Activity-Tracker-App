/*
 * ================ *
 * Global variables *
 * ================ *
 */
var activityLog;  // Stores the user's full (that is, non-filtered) activity log
var distanceUnit; // Stores the user's prefered distance unit (which is obtained from the PHP $_SESSSION['distance_unit'] variable)
/*
 * =================================== *
 * CREATE Event Handlers and Functions *
 * =================================== *
 */
/*
 * Click handler for the Log an Activity button
 */
$('#data-create-button').click(function() {

    // Open the Log an Activity page
    window.location = "create_data.php";
});
/*
 * This function runs when create_data.php is loaded
 */
function initializeCreateDataForm() {
    
    // Hide the Delete button
    $('#data-delete-button').hide();
    
    // Set the data verb to 'create'
    $('#data-verb').val('create');

    // Populate the form
    $('#activity-type').val('Walk');
    var d = new Date();
    var todaysDate = d.getFullYear() + '-' + Number(d.getMonth() + 1).padWithZeros(2, 'left') + '-' + d.getDate().padWithZeros(2, 'left');
    $('#activity-date').val(todaysDate);
    $('#activity-distance').val(0);
    $('#activity-duration-hours').val(0);
    $('#activity-duration-minutes').val(0);
    $('#activity-duration-seconds').val(0);    
}
/*
 * Click event handler for the data forms' Cancel button
 */
$('#data-cancel-button').click(function(e) {

    // Prevent the button default
    e.preventDefault();
    
    // Go back to the home page
    window.location = 'index.php';
    
});
/*
 * Submit handler for the Create/Update form
 */
$('#data-form').submit(function(e) {
    
    // Prevent the default submission
    e.preventDefault();
    
    // Disable the Save button to prevent double submissions
    $('#data-save-button').prop('disabled', true);

    // Convert the data to POST format
    var formData = $(this).serializeArray();
    
    // Submit the data to the handler
    $.post('/handlers/data_handler.php', formData, function(data) {
 
        // Convert the JSON string to a JavaScript object
        var result = JSON.parse(data);
        
        if(result.status === 'error') {
    
            // Display the error
            $('#form-error').html(result.message).css('display', 'inline-block');

            // Enable the Save button
            $('#data-save-button').prop('disabled', false);
            
        } else {
            
            // Display the success message
            $('#form-message').html(result.message).css('display', 'inline-block');

            // Return to the home page after 3 seconds
            window.setTimeout("window.location='index.php'", 3000);            
        }
    });
});
/*
 * ================================= *
 * READ Event Handlers and Functions *
 * ================================= *
 */
/*
 * Read the activities from the server and then display the Activity Log
 */
function readActivities() {

    // Get the user's distance unit
    var formData = [
        {"name": "log-id", "value": $('#log-id').val()},
        {"name": "user-verb", "value": "get-distance-unit"},
        {"name": "token", "value": $('#token').val()}
    ];
   
    // Submit the data to the handler
    $.post('/handlers/user_handler.php', formData, function(data) {

        // Convert the JSON string to a JavaScript object
        var result = JSON.parse(data);
        distanceUnit = result.message;

        // Get the form data and convert it to a POST-able format
        formData = $('#data-read-form').serializeArray();
    
        // Submit the data to the handler
        $.post('/handlers/data_handler.php', formData, function(data) {
            
            // Convert the JSON string to a JavaScript object
            var result = JSON.parse(data);
                    
            // If there was an error, result.status will be defined
            if (typeof result.status !== 'undefined') {
                
                // If so, display the error
                $('#read-error').html(result.message).css('display', 'inline-block');
    
            } else {
                
                // Otherwise, we can go ahead and display the data
                activityLog = result;
                applyFilters();
            }
        });
    });
}
/*
 * Click handler for the Activity Log's Date "From" filter on the home page
 */
$('#activity-filter-date-from').change(function() {
    applyFilters();
});
/*
 * Click handler for the Activity Log's Date "To" filter on the home page
 */
$('#activity-filter-date-to').change(function() {
    applyFilters();
});
/*
 * Click handler for the Activity Log's Type filter button on the home page
 */
$('#activity-filter-type').change(function() {
    applyFilters();
});
/*
 * Applies the current Activity Log filters
 */
function applyFilters() {
    
    // Get the current filter values
    var earliestDateFilter = $('#activity-filter-date-from').val();
    var latestDateFilter = $('#activity-filter-date-to').val();
    var activityTypeFilter = $('#activity-filter-type > option:selected').text();

    // Filter based on the "From" date
    filteredLog = activityLog.filter(function(activity) {
        return activity.date >= earliestDateFilter;
    });
    
    // Filter based on the "To" date
    filteredLog = filteredLog.filter(function(activity) {
        return activity.date <= latestDateFilter;
    });
    
    // Filter based on the "Type" value
    if(activityTypeFilter !== 'All') {
        filteredLog = filteredLog.filter(function(activity) {
            return activity.type === activityTypeFilter;
        });
    }    
    displayActivityLog(filteredLog);
}
/*
 * =================================== *
 * UPDATE Event Handlers and Functions *
 * =================================== *
 */
/*
 * Click handler for the Activity Log's links.
 * Since we created the links in code, we can't use them
 * as jQuery selectors, so we use the closest DOM ancestor,
 * which is the <section id="activity-log") tag. 
 */
$('#activity-log').click(function(e) {
    e.preventDefault();
    
    // Was a link clicked?
    if(e.target.tagName === 'A') {
        
        // If so, go to the linked page
        window.location = e.target;
    }
    // Otherwise, make sure we're dealing with an edit button
    else if(e.target.className === 'data-edit-button') {

        //Get the activity's ID
        var activityID = Number(e.target.id.split('-')[1]);
    
        // Load the Update form and send the activity ID in the query string
        window.location = 'update_data.php?activity-id=' + activityID;
    }
});
/*
 * This function runs when update_data.php is loaded
 */
function initializeUpdateDataForm() {
    
    // Get the activity ID from the URL query string and save it to the form
    var activityID = Number(window.location.search.split('=')[1]);
    $('#activity-id').val(activityID);
    
    // Get the data for this item
    var formData = [
        {"name": "log-id", "value": $('#log-id').val()},
        {"name": "activity-id", "value": $('#activity-id').val()},
        {"name": "data-verb", "value": "read-data-item"},
        {"name": "token", "value": $('#token').val()}
    ];
   
    // Submit the data to the handler
    $.post('/handlers/data_handler.php', formData, function(data) {

        // Convert the JSON string to a JavaScript object
        var result = JSON.parse(data);

        // If there was an error, result.status will be defined
        if (typeof result.status !== 'undefined') {
            
            // If so, display the error
            $('#form-error').html(result.message).css('display', 'inline-block');

        } else {
            // Show the Delete button
            $('#data-delete-button').show();
        
            // Set the data verb to "update"
            $('#data-verb').val('update');
            
            // Store the activity values
            // We know that "result" is a single-item array, so just take the first item
            activity = result[0];
            var activityType = activity.type;
            var activityDate = activity.date
            var activityDistance = Number(activity.distance).toFixed(2);
            var activityDuration = activity.duration.split(':');
            
            // Use the activity values to populate the edit form
            $('#activity-id').val(activityID);
            $('#activity-type').val(activityType);
            $('#activity-date').val(activityDate);
            $('#activity-distance').val(activityDistance);
            $('#activity-duration-hours').val(activityDuration[0]);
            $('#activity-duration-minutes').val(activityDuration[1]);
            $('#activity-duration-seconds').val(activityDuration[2]);
        }
    });
}

/*
 * =================================== *
 * DELETE Event Handlers and Functions *
 * =================================== *
 */
/*
 * Click event handler for the Delete This Activity button in the form
 */
$('#data-delete-button').click(function(e) {
    
    // Take the focus off the button
    $(this).blur();
    
    // Open the jQuery UI dialog
    $('#confirm-delete').dialog('open');
    
    // Prevent the default action
    e.preventDefault();
});
/*
 * Configures and opens the jQuery UI Delete This Activity dialog
 */
$("#confirm-delete").dialog({
    autoOpen: false,
    closeOnEscape: true,
    modal: true,
    width: 400,
    buttons: [
        {
            text: 'Cancel',
            click: function() {
                $(this).dialog('close');
            }
        },
        {
            text: 'Delete',
            click: function() {
                
                // Close the dialog
                $(this).dialog('close');

                // Disable all the buttons
                $('#data-form button').prop('disabled', true);
                
                // Set the data verb to "delete"
                $('#data-verb').val('delete');
                    
                // Get the form data and convert it to a POST-able format
                // We only need the log ID, activity ID, data verb, and token from the form,
                // so we'll build the array by hand instead of using serializeArray()
                formData = [
                    {"name": "log-id", "value": $('#log-id').val()},
                    {"name": "activity-id", "value": $('#activity-id').val()},
                    {"name": "data-verb", "value": $('#data-verb').val()},
                    {"name": "token", "value": $('#token').val()}
                ];
                
                // Submit the data to the handler
                $.post('/handlers/data_handler.php', formData, function(data) {
             
                    // Convert the JSON string to a JavaScript object
                    var result = JSON.parse(data);
                    
                    if(result.status === 'error') {
                
                        // Display the error
                        $('#form-error').html(result.message).css('display', 'inline-block');
            
                        // Enable all the buttons
                        $('#data-form button').prop('disabled', false);
            
                    } else {
                        
                        // Display the success message
                        $('#form-message').html(result.message).css('display', 'inline-block');
            
                        // Return to the home page after 1 second
                        window.setTimeout("window.location='index.php'", 1000);            
                    }
                });
            }
        }
    ]
});
/*
 * ======================================== *
 * COMMON Page Event Handlers and Functions *
 * ======================================== *
 */
/*
 * Display the Activity Log
 */
function displayActivityLog(log) {

    $('.activity-log').html('<div id="activity-log-header" class="activity activity-log-header">');
    $('#activity-log-header').append('<div class="activity-item">Type</div>');
    $('#activity-log-header').append('<div class="activity-item">Date</div>');
    $('#activity-log-header').append('<div class="activity-item">Distance<br>(<a href="your_account.php">' + distanceUnit + '</a>)</div>');
    $('#activity-log-header').append('<div class="activity-item">Duration<br>(hh:mm:ss)</div>');
    $('#activity-log-header').append('<div class="activity-item">Edit</div>');
    $('.activity-log').append('</div>');
    $.each(log, function(index, activity) {
        $('.activity-log').append('<div id="activity' + activity.activity_id + '" class="activity">');
        switch (activity.type) {
            case 'Walk':
                activityIcon = '<img src="images/walk.png" alt="Walk activity icon">';
                break;
            case 'Run':
                activityIcon = '<img src="images/run.png" alt="Run activity icon">';
                break;
            case 'Cycle':
                activityIcon = '<img src="images/cycle.png" alt="Cycle activity icon">';
                break;
        }
        $('#activity' + activity.activity_id).append('<div class="activity-item">' + activityIcon + activity.type + '</div>');
        $('#activity' + activity.activity_id).append('<div class="activity-item">' + activity.date + '</div>');
        $('#activity' + activity.activity_id).append('<div class="activity-item">' + Number(activity.distance).toFixed(2) + '</div>');
        $('#activity' + activity.activity_id).append('<div class="activity-item">' + activity.duration + '</div>');
        $('#activity' + activity.activity_id).append('<div class="activity-item"><input id="activity-' + activity.activity_id + '" class="data-edit-button" type="image" src="images/pencil.png" alt="Pencil icon; click to edit this activity"></div>');
        $('.activity-log').append('</div>');
    });
    
    // Display the summary
    displayActivitySummary(log);
}
/*
 * Summarizes the walks, runs, and rides that appear in the
 * current user's activity log (even if it's filtered).
 */
function displayActivitySummary(log) {
    
    // These variables hold the total number of each activity
    var allCount = 0,
        walkCount = 0,
        runCount = 0,
        cycleCount = 0;
        
    // Calcualte the distance and count for all activities
    var allTotalDistance = log.reduce(function(accumulator, currentActivity) {
        allCount++;
        return accumulator + Number(currentActivity.distance);
    }, 0);
    
    // Calculate the duration (hh:mm:ss) for all activities
    // durationArray[0] = hours
    // durationArray[1] = minutes
    // durationArray[2] = seconds
    var durationArray = log.reduce(function(accumulator, currentActivity) {
        var duration = currentActivity.duration.split(':');
        accumulator[0] += Number(duration[0]);
        accumulator[1] += Number(duration[1]);
        accumulator[2] += Number(duration[2]);
        return accumulator;
    }, [0,0,0]);
    
    // Adjust the hours, seconds, and minutes
    // For example, if durationArray[2] (seconds) equals 100 (i.e., 1 minute, 40 seconds),
    // then change the durationArray[2] value to 40 and add 1 to durationArray[1] (minutes)
    durationArray[1] += quotientAndRemainder(durationArray[2], 60)[0];
    durationArray[2] = quotientAndRemainder(durationArray[2], 60)[1];
    durationArray[0] += quotientAndRemainder(durationArray[1], 60)[0];
    durationArray[1] = quotientAndRemainder(durationArray[1], 60)[1];
    var allTotalDuration = durationArray[0] + ':' + durationArray[1].padWithZeros(2) + ':' + durationArray[2].padWithZeros(2);

    // Calculate the distance and count for the walks
    var walkTotalDistance = log.reduce(function(accumulator, currentActivity) {
        if(currentActivity.type === 'Walk') {
            walkCount++;
            return accumulator + Number(currentActivity.distance);
        } else {
            return accumulator;
        }
    }, 0);

    // Calculate the duration (hh:mm:ss) for walks
    durationArray = log.reduce(function(accumulator, currentActivity) {
        if(currentActivity.type === 'Walk') {
            var duration = currentActivity.duration.split(':');
            accumulator[0] += Number(duration[0]);
            accumulator[1] += Number(duration[1]);
            accumulator[2] += Number(duration[2]);
            return accumulator;
        } else {
            return accumulator;
        }
    }, [0,0,0]);
    
    // Adjust the hours, seconds, and minutes
    durationArray[1] += quotientAndRemainder(durationArray[2], 60)[0];
    durationArray[2] = quotientAndRemainder(durationArray[2], 60)[1];
    durationArray[0] += quotientAndRemainder(durationArray[1], 60)[0];
    durationArray[1] = quotientAndRemainder(durationArray[1], 60)[1];
    var walkTotalDuration = durationArray[0] + ':' + durationArray[1].padWithZeros(2) + ':' + durationArray[2].padWithZeros(2);
    
    // Calculate the distance and count for the runs
        var runTotalDistance = log.reduce(function(accumulator, currentActivity) {
        if(currentActivity.type === 'Run') {
            runCount++;
            return accumulator + Number(currentActivity.distance);
        } else {
            return accumulator;
        }
    }, 0);
    
    // Calculate the duration (hh:mm:ss) for runs
    durationArray = log.reduce(function(accumulator, currentActivity) {
        if(currentActivity.type === 'Run') {
            var duration = currentActivity.duration.split(':');
            accumulator[0] += Number(duration[0]);
            accumulator[1] += Number(duration[1]);
            accumulator[2] += Number(duration[2]);
            return accumulator;
        } else {
            return accumulator;
        }
    }, [0,0,0]);
    
    // Adjust the hours, seconds, and minutes
    durationArray[1] += quotientAndRemainder(durationArray[2], 60)[0];
    durationArray[2] = quotientAndRemainder(durationArray[2], 60)[1];
    durationArray[0] += quotientAndRemainder(durationArray[1], 60)[0];
    durationArray[1] = quotientAndRemainder(durationArray[1], 60)[1];
    var runTotalDuration = durationArray[0] + ':' + durationArray[1].padWithZeros(2, 'left') + ':' + durationArray[2].padWithZeros(2, 'left');

    // Calcualte the distance and count for the rides
    var cycleTotalDistance = log.reduce(function(accumulator, currentActivity) {
        if(currentActivity.type === 'Cycle') {
            cycleCount++;
            return accumulator + Number(currentActivity.distance);
        } else {
            return accumulator;
        }
    }, 0);

    // Calculate the duration (hh:mm:ss) for rides
    durationArray = log.reduce(function(accumulator, currentActivity) {
        if(currentActivity.type === 'Cycle') {
            var duration = currentActivity.duration.split(':');
            accumulator[0] += Number(duration[0]);
            accumulator[1] += Number(duration[1]);
            accumulator[2] += Number(duration[2]);
            return accumulator;
        } else {
            return accumulator;
        }
    }, [0,0,0]);
    
    // Adjust the hours, seconds, and minutes
    durationArray[1] += quotientAndRemainder(durationArray[2], 60)[0];
    durationArray[2] = quotientAndRemainder(durationArray[2], 60)[1];
    durationArray[0] += quotientAndRemainder(durationArray[1], 60)[0];
    durationArray[1] = quotientAndRemainder(durationArray[1], 60)[1];
    var cycleTotalDuration = durationArray[0] + ':' + durationArray[1].padWithZeros(2, 'left') + ':' + durationArray[2].padWithZeros(2, 'left');
    
    // Write the summary data to the <aside> element
    $('aside').html('<h2 role="heading">Activity Summary</h2>');
    $('aside').append('<h3>All Activities</h3>');
    $('aside').append('<div>Total: ' + allCount + '</div>');
    $('aside').append('<div>Distance: ' + allTotalDistance.roundDecimals(2) + ' ' + distanceUnit + '</div>');
    $('aside').append('<div>Duration: ' + allTotalDuration + '</div>');
    $('aside').append('<h3>Walks</h3>');
    $('aside').append('<div>Total: ' + walkCount + '</div>');
    $('aside').append('<div>Distance: ' + walkTotalDistance.roundDecimals(2) + ' ' + distanceUnit + '</div>');
    $('aside').append('<div>Duration: ' + walkTotalDuration + '</div>');
    $('aside').append('<h3>Runs</h3>');
    $('aside').append('<div>Total: ' + runCount + '</div>');
    $('aside').append('<div>Distance: ' + runTotalDistance.roundDecimals(2) + ' ' + distanceUnit + '</div>');
    $('aside').append('<div>Duration: ' + runTotalDuration + '</div>');
    $('aside').append('<h3>Rides</h3>');
    $('aside').append('<div>Total: ' + cycleCount + '</div>');
    $('aside').append('<div>Distance: ' + cycleTotalDistance.roundDecimals(2) + ' ' + distanceUnit + '</div>');
    $('aside').append('<div>Duration: ' + cycleTotalDuration + '</div>');
}
/*
 * Calculates the number of days betwen two dates
 * Both date1 and date2 must be JavaScript Date objects
 */
function daysDiff(date1, date2) {
    
    // First, calculate the absolute difference in milliseconds
    var ms = Math.abs(date1 - date2);
    
    // Now convert milliseconds to days and return it
    var days = ms / (1000 * 60 * 60 * 24);
    return days;
}
/*
 * Subtracts the specified number of days from the date
 * The date must be a JavaScript Date object
 */
function subtractDays(date, days) {
    
    // Convert days to milliseconds
    var ms = days * 1000 * 60 * 60 * 24;
    
    // Subtract it from the date
    var newDate = new Date(date - ms);
    
    // Convert it to a string and return it
    var strDate = newDate.getFullYear() + '-' + Number(newDate.getMonth() + 1) + '-' + newDate.getDate();
    return strDate;
}

function distanceElement(element) {
    return element.name == 'activity-distance';
}
/*
 * Rounds a value to the specified number of decimals
 */
Number.prototype.roundDecimals = function(decimals) {
    var result1 = this * Math.pow(10, decimals);
    var result2 = Math.round(result1);
    var result3 = result2 / Math.pow(10, decimals);
    return result3;
}
/*
 * Pads a value with 0s until the specified length is reached
 * If direction = left, the 0s are added in front of the value
 * If direction = right, the 0s are added after the value
 */
Number.prototype.padWithZeros = function(strLength, direction) {
    var str = String(this);
    while (str.length < (strLength || 2)) {
        if (direction === 'right') {
            str += '0';
        } else {
            str = '0' + str;    
        }
    }
    return str;
}
/*
 * Divides the dividend by the divisor and returns
 * an array that consists of the integer portion of
 * the quotient and the remainder
 */
function quotientAndRemainder(dividend, divisor) {
    var quotientInteger = Math.floor(dividend / divisor);
    var quotientRemainder = dividend % divisor;
    return [quotientInteger, quotientRemainder];
}

