<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

// Setting up New Class form handler
$newClassForm = new Form("POST", [
	new FormTextInput("Course Name", "", "title", "The Course Name", "title"),
	new FormTextInput("College Code", "", "business", "College Code (ITSC/MATH/...)", "code", 4, 4),
	new FormNumberInput("Course Number", "", "format_list_numbered", "The Course Number (1101/1241/...)", "number", 1000, 9999),
	new FormInput("Course End Date", "", "date_range", "The Date the class Ends", "dateEnd", "date"),
	new FormInput("Course Color", "#4caf50", "color_lens", "The (Optional) Color of the class", "color", "color")
], "newClassBtn");

// Setup header
$header = new Header();
$header->active = 0;
$header->search = true;
$header->searchAction = "index.php";

// Include the Header
require "templates/header.php";

?>
						<!-- Page Content Here -->
<?php if (isset($_GET["search"]) && !empty($_GET["search"])): ?>
						<h1>Search for "<?= $_GET["search"] ?>"</h1>
<?php endif; ?>
<?php

// Setup form data processing and uploading
if (isset($_POST["newClassBtn"])) {
	$errors = $newClassForm->updateData();
	if (count($errors) > 0) {
		foreach ($errors as $error) {
			echo "\t\t\t\t\t\t<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
\t\t\t\t\t\t\t<strong>Error: </strong> ${error}
\t\t\t\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
\t\t\t\t\t\t\t\t<span aria-hidden=\"true\">&times;</span>
\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t</div>\n";
		}
	}
	else {
		$result = $newClassForm->process('INSERT INTO `classes` (name, college, courseNumber, dateEnd, color, userId)
			VALUES (${title}, ${code}, ${number}, ${dateEnd}, ${color}, \''.$user["id"].'\')');
		if ($result) {
			echo "\t\t\t\t\t\t<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
\t\t\t\t\t\t\t<strong>Success: </strong> We successfully added your new course to the list!
\t\t\t\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
\t\t\t\t\t\t\t\t<span aria-hidden=\"true\">&times;</span>
\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t</div>\n";
		}
		else {
			errorEmails(
				"Error In Your Website (College Handler)",
				"
There was an error in your website (".$WEBSITE_URL.") while processing a form. Here are the details:
Form Title: New Class Form
SQL Attempted to Run: ".$newClassForm->sql('INSERT INTO `classes` (name, college, courseNumber, dateEnd, color, userId)
	VALUES (${title}, ${code}, ${number}, ${dateEnd}, ${color}, \''.$user["id"].'\')')."

Form Information:

".$newClassForm->template('
${title}
- Name: ${name}
- Data: ${data}
')."

Mysqli Error report:

".mysqli_error($db)
			);
			echo "\t\t\t\t\t\t<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
\t\t\t\t\t\t\t<strong>Error Uploading: </strong> Please try again later, something appaers to be wrong with our servers!
\t\t\t\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
\t\t\t\t\t\t\t\t<span aria-hidden=\"true\">&times;</span>
\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t</div>\n";
		}
	}
}

?>
						<div class="row">
<?php

$query = "";

if (isset($_GET["search"]) && !empty($_GET["search"])) {
	$search = mysqli_real_escape_string($db, $_GET["search"]);
	$query = "SELECT * FROM `classes` WHERE userId='{$user['id']}' AND `dateEnd` > NOW() AND
		(`name` LIKE '%$search%' OR `college` LIKE '%$search%' OR `courseNumber` LIKE '%$search%')";
}
else
	$query = "SELECT * FROM `classes` WHERE userId='{$user['id']}' AND `dateEnd` > NOW()";

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
							<div class="col-md-4">
								<div class="card">
									<div class="card-header card-header-warning">
										<h4 class="card-title">Add a new Course</h4>
										<p class="category">In just a few simple steps!</p>
									</div>
									<div class="card-body">
										<form action="index.php" method="post">
<?php
$newClassForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div>\n");
?>
											<br>
											<button type="submit" name="newClassBtn" class="btn btn-danger">Add Class!</button>
										</form>
									</div>
								</div>
							</div>
						</div>
<?php

// Include the footer
require "templates/footer.php";

?>