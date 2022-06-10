<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
	header("Location: index.php");
	exit();
}

// Select the main class
$class = getData("SELECT * FROM `classes` WHERE userId='".$user['id']."' AND id='".mysqli_real_escape_string($db, $_GET['id'])."'");
// Make sure we find the main class
if (count($class) == 0) {
	header("Location: index.php");
	exit();
}
// Set the main class
$class = $class[0];

// Get the units for the class
$units = getData("SELECT * FROM `units` WHERE userId='".$user['id']."' AND classId='{$class['id']}' ORDER BY unitNumber ASC");

// Create the form for creating new units
$newUnitForm = new Form("POST", [
	new FormTextInput("Unit Name", "", "school", "The name of the new Unit", "name")
], "newUnitBtn");

// Create the form for creating new notes
$newNotesForm = new Form("POST", [
	new FormTextInput("Notes Title", "", "description", "The title for the new notes", "title")
], "newNotesBtn");

// Create the form for creating new lectures
$newLectureForm = new Form("POST", [
	new FormInput("Lecture Start Date", "", "date_range", "The date and time the lecture starts", "startDate", "datetime-local"),
	new FormInput("Lecture End Date", "", "date_range", "The date and time the lecture ends", "endDate", "datetime-local")
], "newLectureBtn");

// The edit lecture form
$editLectureForm = new Form("POST", [
	new FormInput("Lecture Notes", "[]", "", "The linked notes to this lecture", "editLectureNotes", ""),
	new FormNumberInput("Lecture ID", "", "", "The id of the lecture", "editLectureId", 1)
], "editLectureBtn");

// The edit unit form
$editUnitForm = new Form("POST", [
	new FormTextInput("Unit Title", "", "title", "The new unit title", "editUnitTitle"),
	new FormInput("", "", "", "", "editUnit", "", false)
], "editUnitBtn");

// The deadline form
$deadlineForm = new Form("POST", [
	new FormTextInput("Deadline Title", "", "title", "The title of your new deadline", "title"),
	new FormTextInput("Additional Information", "", "info", "Any additional information or notes to add to your deadline", "notes"),
	new FormInput("Deadline Date", "", "date_range", "The date and time to set the deadline at", "deadline", "datetime-local"),
	new FormInput("Deadline Color", $class["color"], "color_lens", "The (Optional) Color of the deadline", "color", "color")
], "deadlineBtn");

// The reoccuring deadline form
$reoccuringDeadlineForm = new Form("POST", [
	new FormTextInput("Deadline Title", "", "title", "The title of your new deadline", "title"),
	new FormTextInput("Additional Information", "", "info", "Any additional information or notes to add to your deadline", "notes"),
	new FormInput("Deadline Time", "", "date_range", "The time to set the deadline at", "time", "time"),
	new FormCheckboxInput("Mondays Checkbox", "done", "Should we display this deadline on Mondays?", "mon"),
	new FormCheckboxInput("Tuesdays Checkbox", "done", "Should we display this deadline on Tuesdays?", "tue"),
	new FormCheckboxInput("Wednesdays Checkbox", "done", "Should we display this deadline on Wednesdays?", "wed"),
	new FormCheckboxInput("Thursdays Checkbox", "done", "Should we display this deadline on Thursdays?", "thu"),
	new FormCheckboxInput("Fridays Checkbox", "done", "Should we display this deadline on Fridays?", "fri"),
	new FormCheckboxInput("Saturdays Checkbox", "done", "Should we display this deadline on Saturdays?", "sat"),
	new FormCheckboxInput("Sundays Checkbox", "done", "Should we display this deadline on Sundays?", "sun"),
	new FormInput("Deadline Color", $class["color"], "color_lens", "The (Optional) Color of the deadline", "color", "color"),
	new FormInput("Reoccuring Deadline Start Date", "", "date_range", "We will start displaying your deadline from this date", "startDate", "date"),
	new FormInput("Reoccuring Deadline End Date", "", "date_range", "We will stop displaying your deadline after this date", "endDate", "date")
], "reoccuringDeadlineBtn");

$deletion = false;
$deletionType = "";

// Process deleting a lecture
if (isset($_POST["deleteLectureId"]) && !empty($_POST["deleteLectureId"]) && is_nan($_POST["deleteLectureId"]) == false) {
	setData("DELETE FROM `lectures` WHERE userId='".$user['id']."' AND id='".mysqli_real_escape_string($db, $_POST["deleteLectureId"])."'");
	$deletion = true;
	$deletionType = "Lecture";
}

// Process deleting an unit
if (isset($_POST["deleteUnit"]) && !empty($_POST["deleteUnit"]) && is_nan($_POST["deleteUnit"]) == false) {
	$unitId = getData("SELECT * FROM `units` WHERE userId='".$user['id']."' AND unitNumber='".mysqli_real_escape_string($db, $_POST["deleteUnit"])."' AND classId='{$class['id']}'");
	if (count($unitId) == 1) {
		$unitId = $unitId[0]["id"];
		setData("DELETE FROM `units` WHERE userId='".$user['id']."' AND id='$unitId'");
		setData("DELETE FROM `notes` WHERE userId='".$user['id']."' AND unitId='$unitId'");
		setData("DELETE FROM `lectures` WHERE userId='".$user['id']."' AND unitId='$unitId'");
		$deletion = true;
		$deletionType = "Unit";
	}
}

// Unit up/down
if (isset($_POST["unitUp"]) || isset($_POST["unitDown"])) {
	$unit = $_POST["unit"];
	if (!isset($unit) || empty($unit) || $unit < 0) die("Invalid unit");
	$otherUnit = null;
	foreach ($units as $u) {
		if ($u["unitNumber"] == $_POST["unit"]) $unit = $u;
		if (isset($_POST["unitUp"]) && $u["unitNumber"] == ((int)$_POST["unit"])-1) $otherUnit = $u;
		if (isset($_POST["unitDown"]) && $u["unitNumber"] == ((int)$_POST["unit"])+1) $otherUnit = $u;
	}
	if ($otherUnit == null) die("Unit not found");

	if (isset($_POST["unitUp"])) {
		$unit["unitNumber"] -= 1;
		$otherUnit["unitNumber"] += 1;
	}
	if (isset($_POST["unitDown"])) {
		$unit["unitNumber"] += 1;
		$otherUnit["unitNumber"] -= 1;
	}

	setData("UPDATE `units` SET unitNumber = {$unit['unitNumber']} WHERE userId='".$user['id']."' AND id = {$unit['id']}");
	setData("UPDATE `units` SET unitNumber = {$otherUnit['unitNumber']} WHERE userId='".$user['id']."' AND id = {$otherUnit['id']}");

	$units = getData("SELECT * FROM `units` WHERE userId='".$user['id']."' AND classId='{$class['id']}' ORDER BY unitNumber ASC");
}


// Setup header
$header = new Header();
$header->active = 0;
$header->search = false;
$header->title = $class["name"];
$header->subTitle = $class["name"];

// Include the Header
require "templates/header.php";

?>
						<script>
							var lectures = [];
							var notes = [];
							var units = [
<?php

foreach ($units as $index=>$unit) {
	echo "
								{
									name: \"{$unit['name']}\",
									number: {$unit['unitNumber']}
								},";
}

?>
							];
						</script>
						<!-- Page Content Here -->
						<form id="deleteLectureForm" style="display: none;" method="post" action="class.php?id=<?= $class['id'] ?>">
							<input name="deleteLectureId" id="deleteLectureId">
						</form>
						<form id="deleteUnitForm" style="display: none;" method="post" action="class.php?id=<?= $class['id'] ?>">
							<input name="deleteUnit" id="deleteUnit">
						</form>
						<button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#unitModal">Add Unit</button>
						<button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#deadlineModal">Add Deadline</button>
						<button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#reoccuringDeadlineModal">Add Reoccuring Deadline</button>
<?php if ($deletion): ?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success</strong> We successfully deleted your <?= $deletionType ?>.
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
<?php endif; ?>
						<!-- New Unit Modal -->
						<div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="unitModalLabel">Add a Unit</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="newUnitForm">
<?php

// Print out the form for creating new units
$newUnitForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div>\n");

?>
											<input style="display: none;" name="newUnitBtn" value="">
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('newUnitForm').submit();">Create Unit</button>
									</div>
								</div>
							</div>
						</div>
						<!-- New Notes Modal -->
						<div class="modal fade" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="notesModalLabel">Add new Notes</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="newNotesForm">
<?php

// Print out the form for creating new notes
$newNotesForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div>\n");

?>
											<input style="display: none;" id="newNotesUnit" name="newNotesUnit" value="">
											<input style="display: none;" name="newNotesBtn" value="">
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('newNotesForm').submit();">Add Notes</button>
									</div>
								</div>
							</div>
						</div>
						<!-- New Lecture Modal -->
						<div class="modal fade" id="lectureModal" tabindex="-1" role="dialog" aria-labelledby="lectureModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="lectureModalLabel">Add a new Lecture</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="newLectureForm">
<?php

// Print out the form for creating new notes
$newLectureForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div>\n");

?>
											<input style="display: none;" id="newLectureUnit" name="newLectureUnit" value="">
											<input style="display: none;" name="newLectureBtn" value="">
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('newLectureForm').submit();">Add Lecture</button>
									</div>
								</div>
							</div>
						</div>
						<!-- Edit Lecture Modal -->
						<div class="modal fade" id="editLectureModal" tabindex="-1" role="dialog" aria-labelledby="editLectureModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="editLectureModalLabel">Edit Lecture</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="editLectureForm">
											<div class="dropdown">
												<button class="btn btn-primary btn-block dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Link Notes to Lecture</button>
												<div class="dropdown-menu" id="editLectureAvailableNotes" aria-labelledby="dropdownMenuButton"></div>
											</div>
											<table class="table">
												<thead>
													<tr>
														<th class="text-center">#</th>
														<th>Linked Notes Title</th>
														<th>-</th>
													</tr>
												</thead>
												<tbody id="editLectureTableBody"></tbody>
											</table>
											<!-- Invisible inputs -->
											<input style="display: none;" id="editLectureNotes" name="editLectureNotes" value="">
											<input style="display: none;" id="editLectureId" name="editLectureId" value="">
											<input style="display: none;" name="editLectureBtn" value="">
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-danger" onclick="deleteLecture(document.getElementById('editLectureId').value)">Delete Lecture</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('editLectureForm').submit();">Save Changes</button>
									</div>
								</div>
							</div>
						</div>
						<!-- Edit Unit Modal -->
						<div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog" aria-labelledby="editUnitModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="editUnitModalLabel">Edit Unit</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="editUnitForm">
<?php

// Print out the form for creating new units
$editUnitForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div>\n");

?>
											<input style="display: none;" name="editUnit" id="editUnit" value="">
											<input style="display: none;" name="editUnitBtn" value="">
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-danger" onclick="deleteUnit(document.getElementById('editUnit').value)">Delete Unit</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('editUnitForm').submit();">Save Changes</button>
									</div>
								</div>
							</div>
						</div>
						<!-- Deadline Modal -->
						<div class="modal fade" id="deadlineModal" tabindex="-1" role="dialog" aria-labelledby="deadlineModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="deadlineModalLabel">Add Deadline</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="deadlineForm">
											<input style="display: none" name="deadlineBtn">
<?php

// Print out the form for creating new deadlines
$deadlineForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<label>\${description}</label>
\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div><br>\n");

?>
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('deadlineForm').submit();">Add Deadline</button>
									</div>
								</div>
							</div>
						</div>
						<!-- Reoccuring Deadline Modal -->
						<div class="modal fade" id="reoccuringDeadlineModal" tabindex="-1" role="dialog" aria-labelledby="reoccuringDeadlineModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="reoccuringDeadlineModalLabel">Add Reocurring Deadline</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="class.php?id=<?= $class["id"] ?>" method="post" id="reoccuringDeadlineForm">
											<input style="display: none" name="reoccuringDeadlineBtn">
<?php

// Print out the form for creating new reoccurring deadlines
$reoccuringDeadlineForm->createForm("\t\t\t\t\t\t\t\t\t\t\t<label>\${description}</label>
\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"input-group-prepend\">
\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"input-group-text\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"material-icons\">\${icon}</i>
\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"\${type}\" name=\"\${name}\" value=\"\${default}\" class=\"form-control\" placeholder=\"\${description}\">
\t\t\t\t\t\t\t\t\t\t\t</div><br>\n");

?>
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary" onclick="document.getElementById('reoccuringDeadlineForm').submit();">Add Reoccuring Deadline</button>
									</div>
								</div>
							</div>
						</div>
						<br>
<?php

/** Handles processing and errors for a single form */
function handleForm($form, $sql, $formTitle) {
	global $db;
	if (isset($_POST[$form->submit])) {
		$errors = $form->updateData();
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				echo "\t\t\t\t\t\t<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
	\t\t\t\t\t\t\t<strong>Error: </strong> ${error}
	\t\t\t\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	\t\t\t\t\t\t\t\t<span aria-hidden=\"true\">&times;</span>
	\t\t\t\t\t\t\t</button>
	\t\t\t\t\t\t</div>\n";
			}
			return false;
		}
		else {
			$result = $form->process($sql);
			if ($result) {
				echo "\t\t\t\t\t\t<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
	\t\t\t\t\t\t\t<strong>Success: </strong> We successfully added/edited your new $formTitle to the list!
	\t\t\t\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	\t\t\t\t\t\t\t\t<span aria-hidden=\"true\">&times;</span>
	\t\t\t\t\t\t\t</button>
	\t\t\t\t\t\t</div>\n";
				return true;
			}
			else {
				global $WEBSITE_URL;
				errorEmails(
					"Error In Your Website (College Handler)",
					"
There was an error in your website (".$WEBSITE_URL.") while processing a form. Here are the details:
Form Title: $formTitle
SQL Attempted to Run: $sql

Form Information:

".$form->template('
${title}
- Name: ${name}
- Data: ${data}
')."

Mysqli Error report:

".mysqli_error($db)
				);
				echo "\t\t\t\t\t\t<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
	\t\t\t\t\t\t\t<strong>Error Uploading: </strong> Oops! Something went wrong with our servers! Please try again later.
	\t\t\t\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
	\t\t\t\t\t\t\t\t<span aria-hidden=\"true\">&times;</span>
	\t\t\t\t\t\t\t</button>
	\t\t\t\t\t\t</div>\n";
				return false;
			}
		}
	}
	return false;
}

// Setup new unit form data processing and uploading and unit editing
if (handleForm($newUnitForm, 'INSERT INTO `units` (classId, name, unitNumber, userId) VALUES (\''.$class["id"].'\', ${name}, \''.(count($units)+1).'\', \''.$user["id"].'\')', "Unit") ||
	handleForm($editUnitForm, 'UPDATE `units` SET name=${editUnitTitle} WHERE userId=\''.$user['id'].'\' AND unitNumber=${editUnit} AND classId=\''.$class["id"].'\'', "Unit"));
	$units = getData("SELECT * FROM `units` WHERE userId='".$user['id']."' AND classId='{$class['id']}' ORDER BY unitNumber ASC");

// Setup the form for editing lectures
handleForm($editLectureForm, 'UPDATE `lectures` SET notesIds=${editLectureNotes} WHERE userId=\''.$user['id'].'\' AND id=${editLectureId}', "Lecture");

// Setup the form for deadlines
handleForm($deadlineForm, 'INSERT INTO `deadlines` (title, notes, deadline, color, classId, userId) VALUES (${title}, ${notes}, ${deadline}, ${color}, \''.$class["id"].'\', \''.$user["id"].'\')', "Deadline");

// Setup the form for reocurring deadlines
if (handleForm($reoccuringDeadlineForm, 'INSERT INTO `reoccurringDeadlines` (title, notes, time, mon, tue, wed, thu, fri, sat, sun, color, startDate, endDate, classId, userId) 
	VALUES (${title}, ${notes}, ${time}, ${mon}, ${tue}, ${wed}, ${thu}, ${fri}, ${sat}, ${sun}, ${color}, ${startDate}, ${endDate}, \''.$class["id"].'\', \''.$user["id"].'\')', "Reoccurring Deadline")) {
	// Get data from form
	$title = mysqli_real_escape_string($db, $reoccuringDeadlineForm->getValue("title"));
	$notes = mysqli_real_escape_string($db, $reoccuringDeadlineForm->getValue("notes"));
	$time = date_create_from_format("H:i", $reoccuringDeadlineForm->getValue("time"));
	$color = mysqli_real_escape_string($db, $reoccuringDeadlineForm->getValue("color"));
	$reoccuringDeadlineId = mysqli_insert_id($db);
	// Encode the start and end dates
	$startDate = new DateTime($reoccuringDeadlineForm->getValue("startDate"));
	$endDate = new DateTime($reoccuringDeadlineForm->getValue("endDate"));

	if (strtotime($reoccuringDeadlineForm->getValue("endDate")) - strtotime($reoccuringDeadlineForm->getValue("startDate")) > 365*60*60*24) {
		die("Your time gap was too big. The reoccurring deadline was added, but we could not add all of the individual deadlines.");
	}

	// Make sure it includes all possible times
	$startDate->setTime(0, 0, 0);
	$endDate->setTime(11, 59, 59);

	// Loop through dates
	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod($startDate, $interval, $endDate);
	foreach ($period as $dt) {
		$val = 0;
		$day = $dt->format('N');
		if ($day == 1) $val = $reoccuringDeadlineForm->getValue("mon");
		if ($day == 2) $val = $reoccuringDeadlineForm->getValue("tue");
		if ($day == 3) $val = $reoccuringDeadlineForm->getValue("wed");
		if ($day == 4) $val = $reoccuringDeadlineForm->getValue("thu");
		if ($day == 5) $val = $reoccuringDeadlineForm->getValue("fri");
		if ($day == 6) $val = $reoccuringDeadlineForm->getValue("sat");
		if ($day == 7) $val = $reoccuringDeadlineForm->getValue("sun");

		$dt->setTime($time->format("H"), $time->format("i"));

		if ($val) {
			setData("INSERT INTO `deadlines` (title, notes, deadline, color, reoccurringDeadlineId, classId, userId)
				VALUES ('".$title."', '".$notes."', '".$dt->format("Y-m-d H:i:s")."', '".$color."', '".$reoccuringDeadlineId."', '".$class["id"]."', '".$user["id"]."')");
		}
	}
}

?>
						<div class="accordion" id="unitAccordion">
<?php foreach ($units as $unit): ?>
<?php

$notes = getData("SELECT * FROM `notes` WHERE userId='".$user['id']."' AND classId={$class['id']} AND unitId={$unit['id']} ORDER BY dateCreated ASC");
$lectures = getData("SELECT * FROM `lectures` WHERE userId='".$user['id']."' AND classId={$class['id']} AND unitId={$unit['id']} ORDER BY timeStarted ASC");

?>
							<div class="card">
								<div id="unitHeading<?= $unit["unitNumber"] ?>" class="card-header card-header-primary" style="background: <?= $class["color"] ?> !important;">
									<div class="row">
										<div class="col-md-9">
											<a class="<?= $unit["collapsed"] ? "collapsed" : "" ?>" style="cursor: pointer; display: block;" type="button" data-toggle="collapse" data-target="#unitCollapse<?= $unit["unitNumber"] ?>" aria-expanded="<?= $unit["collapsed"] ? "false" : "true" ?>" aria-controls="unitCollapse<?= $unit["unitNumber"] ?>">
												<h4 class="card-title"><?= $unit["unitNumber"].". ".$unit["name"] ?></h4>
											</a>
										</div>
										<div class="col-md-1">
											<button type="button" style="box-shadow: none;" class="btn btn-primary bg-transparent btn-fab btn-fab-mini btn-round" data-toggle="modal" data-target="#editUnitModal" onclick="document.getElementById('editUnit').value = <?= $unit['unitNumber'] ?>;">
												<i class="material-icons">edit</i>
											</button>
										</div>
										<div class="col-md-1">
<?php if ($unit["unitNumber"] != count($units)): ?>
											<form method="post" action="class.php?id=<?= $class["id"] ?>">
												<input style="display: none;" name="unit" value="<?= $unit["unitNumber"] ?>">
												<button name="unitDown" type="submit" style="box-shadow: none;" class="btn btn-primary bg-transparent btn-fab btn-fab-mini btn-round">
													<i class="material-icons">keyboard_arrow_down</i>
												</button>
											</form>
<?php endif; ?>
										</div>
										<div class="col-md-1">
<?php if ($unit["unitNumber"] != 1): ?>
											<form method="post" action="class.php?id=<?= $class["id"] ?>">
												<input style="display: none;" name="unit" value="<?= $unit["unitNumber"] ?>">
												<button name="unitUp" type="submit" style="box-shadow: none;" class="btn btn-primary bg-transparent btn-fab btn-fab-mini btn-round">
													<i class="material-icons">keyboard_arrow_up</i>
												</button>
											</form>
<?php endif; ?>
										</div>
									</div>
								</div>
								<div unit="<?= $unit["unitNumber"] ?>" id="unitCollapse<?= $unit["unitNumber"] ?>" aria-labelledby="unitHeading<?= $unit["unitNumber"] ?>" class="collapse <?= $unit["collapsed"] ? "" : "show" ?>">
									<div class="card-body">
										<div class="row">
											<div class="col-md-4">
												<table class="table">
													<thead>
														<tr>
															<th class="text-center">#</th>
															<th>Notes Title</th>
															<th>Date Created</th>
														</tr>
													</thead>
													<tbody>
<?php foreach ($notes as $index=>$note): ?>
<?php
echo "<script>
	notes.push({
		id: {$note['id']},
		title: '{$note['title']}',
		unit: {$unit['unitNumber']}
	});
</script>";
?>
														<!-- Table Body -->
														<tr>
															<td class="text-center"><?= $index+1 ?></td>
															<td><a class="btn-link" href="note.php?id=<?= $note["id"] ?>"><?= $note["title"] ?></a></td>
															<td><?= date("g:i A l, F j\\t\\h Y", strtotime($note["dateCreated"])) ?></td>
														</tr>
<?php endforeach; ?>
													</tbody>
												</table>
<?php if (count($notes) == 0): ?>
												<p style="text-align: center; margin: 50px 0;" class="text-muted">You currently have no Notes!</p>
<?php endif; ?>
												<button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#notesModal" onclick="document.getElementById('newNotesUnit').value = <?= $unit['id'] ?>;">Add New Notes</button>
<?php if (isset($_POST["newNotesUnit"]) && $_POST["newNotesUnit"] == $unit["id"]) handleForm($newNotesForm, 'INSERT INTO `notes` (classId, title, unitId, notes, userId) VALUES (\''.$class["id"].'\', ${title}, \''.$unit["id"].'\', \'These notes are empty!\', \''.$user["id"].'\')', "Notes"); ?>
											</div>
											<div class="col-md-8">
												<table class="table">
													<thead>
														<tr>
															<th class="text-center">#</th>
															<th>Lecture Notes</th>
															<th class="text-center">Time Started</th>
															<th class="text-center">Time Ended</th>
															<th class="text-center">Edit</th>
														</tr>
													</thead>
													<tbody>
<?php foreach ($lectures as $index=>$lecture): ?>
<?php

echo "
<script>
	lectures.push({
		id: {$lecture['id']},
		unit: {$unit['unitNumber']},
		notes: {$lecture['notesIds']}
	});
</script>";

$decoded = json_decode($lecture["notesIds"]);
$lectureNotes = [];
if (count($decoded) > 0) $lectureNotes = getData("SELECT * FROM `notes` WHERE userId='".$user['id']."' AND id IN (".implode(',', $decoded).")");

?>
														<!-- Table Body -->
														<tr>
															<td class="text-center"><?= $index+1 ?></td>
															<td>
<?php foreach ($lectureNotes as $note): ?>
																<a style="display: block;" class="btn-link" href="note.php?id=<?= $note['id'] ?>"><?= $note["title"] ?></a>
<?php endforeach; ?>
<?php if (count($lectureNotes) == 0): ?>
																This lecture is associated with no notes!
<?php endif; ?>
															</td>
															<td class="text-center"><?= date("g:i A", strtotime($lecture["timeStarted"])) ?></td>
															<td class="text-center"><?= date("g:i A n/j/y", strtotime($lecture["timeEnded"])) ?></td>
															<td class="text-center td-actions">
																<button type="button" rel="tooltip" class="btn btn-success btn-round" data-toggle="modal" data-target="#editLectureModal" onclick="document.getElementById('editLectureId').value = <?= $lecture['id'] ?>;">
																	<i class="material-icons">edit</i>
																</button>
															</td>
														</tr>
<?php endforeach; ?>
													</tbody>
												</table>
<?php if (count($lectures) == 0): ?>
												<p style="text-align: center; margin: 50px 0;" class="text-muted">You currently have no Lectures!</p>
<?php endif; ?>
												<button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#lectureModal" onclick="document.getElementById('newLectureUnit').value = <?= $unit['id'] ?>;">Add a New Lecture</button>
<?php if (isset($_POST["newLectureUnit"]) && $_POST["newLectureUnit"] == $unit["id"]) handleForm($newLectureForm, 'INSERT INTO `lectures` (classId, unitId, notesIds, timeStarted, timeEnded, userId) VALUES (\''.$class["id"].'\', \''.$unit["id"].'\', \'[]\', ${startDate}, ${endDate}, \''.$user["id"].'\')', "Lecture"); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
<?php endforeach; ?>
						</div>
						<!-- Script for collapse updating and editing lectures -->
						<script>
							// Update collapse to server
							$('#unitAccordion').on('hidden.bs.collapse', function (e) {
								setCollapse(e.target.attributes.unit.value, true);
							});
							$('#unitAccordion').on('shown.bs.collapse', function (e) {
								setCollapse(e.target.attributes.unit.value, false);
							});

							function setCollapse(unit, collapse) {
								$.ajax({
									type: "POST",
									url: "actions/updateCollapse.php",
									dataType: "text",
									data: {
										"classId": <?= $class["id"] ?>,
										"unit": unit,
										"collapse": collapse,
										"apiKey": "<?= $user["apiKey"] ?>"
									},
									success: function(resultData) {}
								});
							}

							// Update edit lecture modal
							var originalNotes = [];

							$("#editLectureModal").on('show.bs.modal', function() {
								var id = $("#editLectureId").val();
								originalNotes = Object.assign(originalNotes, lectures.find((l) => l.id == id).notes);
								updateLectureModal(id);
							});

							$("#editLectureModal").on('hidden.bs.modal', function() {
								var id = $("#editLectureId").val();
								lectures.find((l) => l.id == id).notes = originalNotes;
								originalNotes = [];
							});

							function updateLectureModalNotes(id, notesId) {
								lectures.find((l) => l.id == id).notes.push(notesId);
								updateLectureModal(id);
							}

							function removeLectureModalNotes(id, notesId) {
								var lecture = lectures.find((l) => l.id == id);
								lecture.notes.splice(lecture.notes.indexOf(notesId), 1);
								updateLectureModal(id);
							}

							function updateLectureModal(id) {
								var lecture = lectures.find((l) => l.id == id);
								var selectedNotes = notes.filter((n) => lecture.notes.find((ln) => ln == n.id));
								var availableNotes = notes.filter((n) => n.unit == lecture.unit && selectedNotes.find((sn) => sn.id == n.id) == undefined);
								var availableNotesHtml = "";
								for (var i = 0; i < availableNotes.length; i++)
									availableNotesHtml += `<a class="dropdown-item" href="javascript:void(0)" type="button" onclick="updateLectureModalNotes(${id}, ${availableNotes[i].id})">${availableNotes[i].title}</a>`;
								$("#editLectureAvailableNotes").html(availableNotesHtml);
								var selectedNotesHtml = "";
								for (var i = 0; i < selectedNotes.length; i++)
									selectedNotesHtml += `<tr>
										<td class="text-center">${(i+1)}</td>
										<td>${selectedNotes[i].title}</td>
										<td class="text-center td-actions">
											<button type="button" rel="tooltip" class="btn btn-danger btn-round" onclick="removeLectureModalNotes(${id}, ${selectedNotes[i].id})">
												<i class="material-icons">remove</i>
											</button>
										</td>
									</tr>`;
								$("#editLectureTableBody").html(selectedNotesHtml);

								$("#editLectureNotes").val(JSON.stringify(lecture.notes));
							}

							function deleteLecture(id) {
								document.getElementById("deleteLectureId").value = id;
								document.getElementById("deleteLectureForm").submit();
							}

							function deleteUnit(num) {
								document.getElementById("deleteUnit").value = num;
								document.getElementById("deleteUnitForm").submit();
							}
						</script>
<?php

// Include the footer
require "templates/footer.php";

?>