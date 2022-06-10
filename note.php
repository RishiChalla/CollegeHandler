<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
	header("Location: index.php");
	exit();
}

// Select the main class
$notes = getData("SELECT * FROM `notes` WHERE id='".mysqli_real_escape_string($db, $_GET['id'])."' AND userId='".$user['id']."'");
// Make sure we find the main class
if (count($notes) == 0) {
	die($user['id']);
	header("Location: index.php");
	exit();
}
// Set the main class
$notes = $notes[0];

// Delete the notes if necessary
if (isset($_POST["deleteNotes"])) {
	setData("DELETE FROM `notes` WHERE id='{$notes["id"]}' AND userId='".$user['id']."'");
	$lectures = getData("SELECT * FROM `lectures` WHERE unitId='{$notes['unitId']}' AND userId='".$user['id']."'");
	foreach ($lectures as $lecture) {
		$lectureNotes = json_decode($lecture["notesIds"]);
		if (($key = array_search($notes["id"], $lectureNotes)) !== false) {
			unset($lectureNotes[$key]);
			setData("UPDATE `lectures` SET notesIds='".json_encode(array_values($lectureNotes))."' WHERE id='{$lecture['id']}' AND userId='".$user['id']."'");
		}
	}
	header("Location: class.php?id=".$notes["classId"]);
	exit();
}

// Setup header
$header = new Header();
$header->active = 3;
$header->title = "Notes - ".$notes["title"];
$header->subTitle = "Notes - ".$notes["title"]." (Made at ".date("g:i A l, F j\\t\\h Y", strtotime($notes["dateCreated"])).")<span id='saved'></span>";
$header->containerFluid = false;
$header->additionalNav = '<li class="nav-item">
<a class="nav-link" type="button" onclick="deleteNotes()" style="cursor: pointer">
	<i class="material-icons">delete</i>
	<p class="d-lg-none d-md-block">
		Delete
	</p>
</a>
</li>
<li class="nav-item">
	<a class="nav-link" href="javascript:void(0);" onclick="save();">
		<i class="fa fa-cloud" aria-hidden="true"></i>
		<p class="d-lg-none d-md-block">
			Save
		</p>
	</a>
</li>
<li class="nav-item">
	<a class="nav-link" href="class.php?id='.$notes["classId"].'">
		<i class="fa fa-graduation-cap" aria-hidden="true"></i>
		<p class="d-lg-none d-md-block">
			Classroom
		</p>
	</a>
</li>';

// Add Markdown editor JS and CSS
array_push($js, $WEBSITE_URL."sparksuite-markdown-editor/src/js/simplemde.js");
array_push($js, $WEBSITE_URL."js/note.js");
if ($user["darkMode"] == 0) array_push($css, $WEBSITE_URL."sparksuite-markdown-editor/src/css/simplemde.css");
else array_push($css, "https://cdn.rawgit.com/xcatliu/simplemde-theme-dark/master/dist/simplemde-theme-dark.min.css");
array_push($css, $WEBSITE_URL."css/note.css");

// Include the Header
require "templates/header.php";

?>
						<!-- Page Content Here -->
						<script>
							var notesId = <?= $notes["id"] ?>;
							var preview = <?= $notes["preview"] ? "true" : "false" ?>;
							var apiKey = "<?= $user["apiKey"] ?>";
						</script>
						<form style="display: none;" method="post" id="deleteNotes" action="note.php?id=<?= $notes['id'] ?>">
							<input name="deleteNotes">
						</form>
						<textarea id="editor"><?= $notes["notes"] ?></textarea>
<?php

// Include the footer
require "templates/footer.php";

?>