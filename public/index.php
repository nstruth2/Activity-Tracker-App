<?php
    include_once '../private/common/initialization.php';
    if(isset($_SESSION['username'])) {
        $page_title = 'Your Activity Log';
    } else {
        $page_title = 'Welcome to FootPower!';
    }
    include_once 'common/top.php';
?>
<?php
    if(isset($_SESSION['username'])):
?>    
            <!-- The toolbar contains controls that enable the user to filter the Activity Log 
                 using either a range of dates or by selecting an activity type -->
            <div class="activity-log-toolbar" role="toolbar">
                <label for="activity-filter-date-from">From </label>
                <input id="activity-filter-date-from" class="activity-filter" type="date" value="<?php echo date('Y-m-d', strtotime('-30 days')) ?>">
                <label for="activity-filter-date-to"> to </label>
                <input id="activity-filter-date-to" class="activity-filter" type="date" value="<?php echo date('Y-m-d') ?>">
                <label for="activity-filter-type">Type</label>
                <select id="activity-filter-type" class="activity-filter">
                    <option id="activity-filter-type-all">All</option>
                    <option id="activity-filter-type-walk">Walk</option>
                    <option id="activity-filter-type-run">Run</option>
                    <option id="activity-filter-type-cycle">Cycle</option>
                </select>
                <button id="data-create-button" class="btn" role="button">Add New</button>
            </div>

            <!-- The Activity Log appears here -->
            <section id="activity-log" class="activity-log">
            </section>

            <!-- This hidden form contains the values we need to read the data: log-id, data-verb, and token -->
             <form id="data-read-form" class="hidden">
                <input type="hidden" id="log-id" name="log-id" value="<?php echo $_SESSION['log_id']; ?>">
                <input type="hidden" id="data-verb" name="data-verb" value="read-all-data">
                <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token']; ?>">
            </form>
            
            <!-- If there's an error reading the data, the error message appears inside this span -->
            <span id="read-error" class="error error-message"></span>

<?php
    else:
?>
            <section class="footpower-intro" role="contentinfo">
                <p>
                    Are you a walker, a runner, or a cyclist? Heck, maybe you’re all three! Either way, you know the joy and satisfaction of propelling yourself across the face of the Earth using nothing but the power of your own two feet.
                </p>
                <p>
                    Have you walked, ran, or cycled recently? If so, we salute you! But why relegate the details of that activity to the dim mists of history and memory? Why not save your effort for posterity? Just sign up for a free FootPower! account and you’ll never forget a walk, run, or ride again!
                </p>
                <div>
                    <img src="images/walk-large.png" alt="Drawing of a walker.">
                    <img src="images/run-large.png" alt="Drawing of a runner.">
                    <img src="images/cycle-large.png" alt="Drawing of a syclist.">
                </div>
            </section>
<?php
    endif;
    include_once 'common/sidebar.php';
    include_once 'common/bottom.php';
?>
