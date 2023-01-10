<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FootPower! | <?php echo $page_title ?></title>
    <link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700" rel="stylesheet">
    <link href="/css/jquery-ui.css" rel="stylesheet">
    <link href="/css/styles.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="/js/jquery-ui.js"></script>
    <script>
        $(document).ready(function() {
            
            // Get the current filename and run the code for that file
            var currentURL = window.location.pathname;
            var currentFile = currentURL.substr(currentURL.lastIndexOf('/') + 1);
            switch (currentFile) {

                // Display the signed-in user's Activity Log
                case 'index.php':
                    readActivities();
                    break;
                    
                // Set up the Create Data form
                case 'create_data.php':
                    initializeCreateDataForm();
                    break;

                // Set up the Edit Data form
                case 'update_data.php':
                    initializeUpdateDataForm();
                    break;
            }
        });
    </script>
</head>
<body>
    <header class="top-header" role="banner">
        <div class="top-header-logo">
            <a href="/index.php"><img src="images/footpower-logo.png" alt="FootPower! logo"></a>
        </div>
        <div class="top-header-user">
<?php
    if(isset($_SESSION['username'])):
?>    
            <button id="show-user-account-button" class="btn-plain">Your Account</button>
            <button id="user-sign-out-button" class="btn">Sign Out</button>
<?php
    else:
?>    
            <button id="show-sign-in-page-button" class="btn-plain">Sign In</button>
            <button id="show-sign-up-page-button" class="btn">Sign Up</button>
<?php
    endif;
?>    
        </div>
    </header>
    <main role="main">
        <article role="contentinfo">
            <header class="article-header" role="banner">
                <div class="header-title">
                    <h1><?php echo $page_title ?></h1>
                </div>
            </header>
