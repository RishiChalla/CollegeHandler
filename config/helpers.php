<?php

/**
 * Returns an array of rows given a select query
 */
function getData($query) {
	global $db, $WEBSITE_URL;
	$result = mysqli_query($db, $query);
	if (!$result) {
		errorEmails(
			"Error In Your Website (College Handler)",
			"
	There was an error in your website (".$WEBSITE_URL.") while trying to perform a sql query. Here are the details:
	SQL Attempted to Run: $query
	
	Mysqli Error report:
	
	".mysqli_error($db)
		);
		die("We appear to be having technical difficulties. Please try again later!");
	}

	$data = [];
	while ($row = mysqli_fetch_assoc($result)) {
		array_push($data, $row);
	}

	return $data;
}

/**
 * Returns an array of rows given a update/insert query
 */
function setData($query) {
	global $db, $WEBSITE_URL;
	$result = mysqli_query($db, $query);
	if (!$result) {
		errorEmails(
			"Error In Your Website (College Handler)",
			"
	There was an error in your website (".$WEBSITE_URL.") while trying to perform a sql query. Here are the details:
	SQL Attempted to Run: $query
	
	Mysqli Error report:
	
	".mysqli_error($db)
		);
		die("We appear to be having technical difficulties. Please try again later!");
	}
}

function errorEmails($subject, $message) {
	global $ADMIN_EMAILS;
	foreach ($ADMIN_EMAILS as $email) {
		mail($email, $subject, $message);
	}
}

/** Returns the current time with the timezone difference */
function currentTime() {
	global $TIMEZONE_DIFFERENCE;
	return time() - ($TIMEZONE_DIFFERENCE*3600);
}

/** Returns an alert */
function alert($type, $message) {
	return "<div class=\"alert alert-$type alert-dismissible fade show\" role=\"alert\">
		<strong>".ucfirst($type)."</strong>: $message
		<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
			<span aria-hidden=\"true\">&times;</span>
		</button>
	</div>";
}

?>