        </article>
        <aside role="complementary">
<?php

    // Is the user signed in?
    if(isset($_SESSION['username'])):
        // If so, display the latest site news
?>
        <h2 role="heading">FootPower! News</h2>
        <section>
            <div>
                <strong>February 12, 2018</strong>
            </div>
            <div>
                New feature: If you feel like changing your FootPower! password, head over to the <a href="/request_new_password.php">Request New Password</a> page and we’ll send you a password reset link via email.
            </div>
        </section>
        <section>
            <div>
                <strong>January 15, 2018</strong>
            </div>
            <div>
                New feature: You can now easily switch between miles and kilometers! Go to the <a href="/your_account.php">Your Account</a> page and use the Select a Distance Unit radio buttons to choose your preferred unit.
            </div>
        </section>
        <section>
            <div>
                <strong>January 1, 2018</strong>
            </div>
            <div>
                Release date is here! FootPower! was released into the wild on January 1, 2018. Woo hoo! Thanks to everyone who helped make this a reality.
            </div>
        </section>
<?php
    else:
        // Otherwise, display some app testimonials
?>
        <h2 role="heading">FootPower! Buzz</h2>
            <section>
                “&starf;&starf;&starf;&starf; I give this app one star for each of the toes on my left foot. Yes, I know, there are only four stars. Don’t ask.”<br>—C. Bratton, Scranton, PA
            </section>
            <section>
                “I love this app! I also love exclamation points!! This app should have more exclamation points!!! Please rename this app to FootPower!!!!”<br>—H. Golightly, New York, NY
            </section>
            <section>
                “I have two left feet, but this app still worked great for me. Nice work!”<br>—M. Vanilli, Walla Walla, WA
            </section>
<?php
    endif;
?>
        </aside>