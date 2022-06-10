<?php

// All variables needed to maintain the site are available here.
// Security injections are easily possible as multiple users was never intended for use by this program.
// If anyone ever reads this, please do NOT make your PHP site anywhere similar to this.

global $WEBSITE_URL, $css, $js, $nav, $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT, $ADMIN_EMAILS, $CAPTCHA_KEYS;

/** The main website URL */
$WEBSITE_URL = "";

/** All CSS links to include */
$css = [
	"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons",
	"https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/css/material-dashboard.min.css"
];

/** All JS sources to include */
$js = [
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/core/jquery.min.js",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/core/popper.min.js",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/core/bootstrap-material-design.min.js",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/plugins/perfect-scrollbar.jquery.min.js",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/plugins/chartist.min.js",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/plugins/bootstrap-notify.js",
	$WEBSITE_URL."material-dashboard-dark-edition-v2.1.0/assets/js/material-dashboard.min.js",
	"https://www.google.com/recaptcha/api.js"
];

/** The navigation menu */
$nav = [
	[
		"icon" => "dashboard",
		"link" => $WEBSITE_URL."index.php",
		"text" => "Dashboard"
	],
	[
		"icon" => "restore",
		"link" => $WEBSITE_URL."oldClasses.php",
		"text" => "Old Classes"
	],
	[
		"icon" => "calendar_today",
		"link" => $WEBSITE_URL."calendar.php",
		"text" => "Calendar"
	],
	[
		"icon" => "menu_book",
		"link" => $WEBSITE_URL."lectures.php",
		"text" => "Lectures"
	],
	[
		"icon" => "sticky_note_2",
		"link" => $WEBSITE_URL."notes.php",
		"text" => "Notes"
	],
	[
		"icon" => "privacy_tip",
		"link" => $WEBSITE_URL."privacyPolicy.php",
		"text" => "Privacy Policy"
	],
	[
		"icon" => "done",
		"link" => $WEBSITE_URL."termsAndConditions.php",
		"text" => "Terms and Conditions"
	],
	[
		"icon" => "info",
		"link" => $WEBSITE_URL."disclaimer.php",
		"text" => "Disclaimer"
	],
	[
		"icon" => "contact_support",
		"link" => $WEBSITE_URL."contact.php",
		"text" => "Contact"
	]
];

/** MYSQL Host */
$DB_HOST = "localhost";

/** MYSQL Username */
$DB_USER = "";

/** MYSQL Password */
$DB_PASS = "";

/** MYSQL Database Name */
$DB_NAME = "";

/** MYSQL Port */
$DB_PORT = NULL;

/** The admin emails to send weekly reports to */
$ADMIN_EMAILS = [""];

/** The reCaptcha keys */
$CAPTCHA_KEYS = [
	"server" => "",
	"client" => ""
]

?>