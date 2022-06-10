<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

// Setup header
$header = new Header();
$header->active = 3;

// Load lectures and corresponding data
$query = "SELECT * FROM `lectures` WHERE userId='".$user['id']."' ORDER BY timeStarted DESC LIMIT 100";
$lectures = getData($query);
$notes = [];
$units = [];
$classes = [];

foreach ($lectures as $lecture) {
    $lecture["notesIds"] = json_decode($lecture["notesIds"]);

    foreach ($lecture["notesIds"] as $noteId) {
        $noteFound = false;
        foreach ($notes as $note) {
            if ($note["id"] == $noteId) {
                $unitFound = true;
                break;
            }
        }

        if (!$noteFound) array_push($notes, getData("SELECT * FROM `notes` WHERE userId='".$user['id']."' AND id=$noteId")[0]);
    }

    $unitFound = false;
    foreach ($units as $unit) {
        if ($unit["id"] == $lecture["unitId"]) {
            $unitFound = true;
            break;
        }
    }

    if (!$unitFound) array_push($units, getData("SELECT * FROM `units` WHERE userId='".$user['id']."' AND id=".$lecture["unitId"])[0]);

    $classFound = false;
    foreach ($classes as $class) {
        if ($class["id"] == $lecture["classId"]) {
            $classFound = true;
            break;
        }
    }

    if (!$classFound) array_push($classes, getData("SELECT * FROM `classes` WHERE userId='".$user['id']."' AND id=".$lecture["classId"])[0]);
}

// Include the Header
require "templates/header.php";

?>
                        <!-- Page Content Here -->
                        <h1>Recent Lectures:</h1>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Class</th>
                                    <th>Unit</th>
                                    <th>Notes</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                </tr>
                            </thead>
                            <tbody>
<?php 
foreach ($lectures as $index=>$lecture):
    $class = null;
    $unit = null;
    $notesL = [];
    foreach($classes as $classCH) {
        if ($lecture["classId"] == $classCH["id"]) {
            $class = $classCH;
            break;
        }
    }
    foreach($units as $unitCH) {
        if ($lecture["unitId"] == $unitCH["id"]) {
            $unit = $unitCH;
            break;
        }
    }
    $lecture["notesIds"] = json_decode($lecture["notesIds"]);
    foreach ($lecture["notesIds"] as $noteId) {
        foreach($notes as $noteCH) {
            if ($noteId == $noteCH["id"]) {
                array_push($notesL, $noteCH);
                break;
            }
        }
    }
?>
                                <tr>
                                    <td class="text-center"><?= $index+1 ?></td>
                                    <td><a class="btn-link" href="class.php?id=<?= $class["id"] ?>"><?= $class["name"] ?></a></td>
                                    <td><a class="btn-link" href="class.php?id=<?= $class["id"] ?>#unitHeading<?= $unit["unitNumber"] ?>">Unit <?= $unit["unitNumber"] ?></a></td>
                                    <td>
<?php foreach ($notesL as $note): ?>
                                        <a class="btn-link" style="display: block;" href="note.php?id=<?= $note["id"] ?>"><?= $note["title"] ?></a>
<?php endforeach; ?>
                                    </td>
                                    <td><?= date("g:i A", strtotime($lecture["timeStarted"])) ?></td>
                                    <td><?= date("g:i A l, F j\\t\\h Y", strtotime($lecture["timeEnded"])) ?></td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
<?php

// Include the footer
require "templates/footer.php";

?>