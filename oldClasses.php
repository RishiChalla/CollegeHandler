<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

// Setup header
$header = new Header();
$header->active = 1;
$header->title = "Old Classes";
$header->subTitle = "Old Classes";
$header->search = true;
$header->searchAction = "oldClasses.php";

// Include the Header
require "templates/header.php";

?>
						<!-- Page Content Here -->
<?php if (isset($_GET["search"]) && !empty($_GET["search"])): ?>
						<h1>Search for "<?= $_GET["search"] ?>"</h1>
<?php endif; ?>
						<div class="row">
<?php

$query = "";

if (isset($_GET["search"]) && !empty($_GET["search"])) {
	$search = mysqli_real_escape_string($db, $_GET["search"]);
	$query = "SELECT * FROM `classes` WHERE userId='{$user['id']}' AND `dateEnd` < NOW() AND
		(`name` LIKE '%$search%' OR `college` LIKE '%$search%' OR `courseNumber` LIKE '%$search%')";
}
else
	$query = "SELECT * FROM `classes` WHERE userId='{$user['id']}' AND `dateEnd` < NOW()";

$result = mysqli_query($db, $query);
if (!$result) {
	errorEmails(
		"Error In Your Website (College Handler)",
		"
There was an error in your website (".$WEBSITE_URL.") while trying to load classes. Here are the details:
User Email: ".$user["email"]."
SQL Attempted to Run: $query

Mysqli Error report:

".mysqli_error($db)
	);
	die("We appear to be experiencing technical difficulties! Please try again later.");
}

while ($row = mysqli_fetch_assoc($result)) {
	echo "\t\t\t\t\t\t\t<div class=\"col-md-4\">
\t\t\t\t\t\t\t\t<div class=\"card\">
\t\t\t\t\t\t\t\t\t<div class=\"card-header card-header-primary\" style=\"background: {$row['color']} !important;\">
\t\t\t\t\t\t\t\t\t\t<h4 class=\"card-title\">{$row['name']}</h4>
\t\t\t\t\t\t\t\t\t\t<p class=\"category\">{$row['college']} {$row['courseNumber']}</p>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t<div class=\"card-body text-center\">
\t\t\t\t\t\t\t\t\t\tClick on the button below to view units, notes, and lectures related to this class!
\t\t\t\t\t\t\t\t\t\t<br>
\t\t\t\t\t\t\t\t\t\t<a href=\"{$WEBSITE_URL}class.php?id={$row['id']}\" class=\"btn-primary btn\">View Class</a>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>\n";
}

?>
						</div>
<?php

// Include the footer
require "templates/footer.php";

?>