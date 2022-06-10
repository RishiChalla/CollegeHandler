<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

// Setup header
$header = new Header();
$header->active = 4;
$header->search = true;
$header->searchAction = "notes.php";

// Load notes and corresponding data
$query = "SELECT * FROM `notes` WHERE userId='".$user['id']."' ORDER BY dateCreated DESC LIMIT 50";
if (isset($_GET["search"]) && !empty($_GET["search"])) {
    $search = mysqli_real_escape_string($db, $_GET["search"]);
    $query = "SELECT * FROM `notes` WHERE userId='".$user['id']."' AND `title` LIKE '%$search%' ORDER BY dateCreated DESC LIMIT 50";
}
$notes = getData($query);
$units = [];
$classes = [];

foreach ($notes as $note) {
    $unitFound = false;
    foreach ($units as $unit) {
        if ($unit["id"] == $note["unitId"]) {
            $unitFound = true;
            break;
        }
    }

    if (!$unitFound) array_push($units, getData("SELECT * FROM `units` WHERE userId='".$user['id']."' AND id=".$note["unitId"])[0]);

    $classFound = false;
    foreach ($classes as $class) {
        if ($class["id"] == $note["classId"]) {
            $classFound = true;
            break;
        }
    }

    if (!$classFound) array_push($classes, getData("SELECT * FROM `classes` WHERE userId='".$user['id']."' AND id=".$note["classId"])[0]);
}

// Include the Header
require "templates/header.php";

?>
                        <!-- Page Content Here -->
<?php if (isset($_GET["search"]) && !empty($_GET["search"])): ?>
                        <h1>Search for "<?= $_GET["search"] ?>"</h1>
<?php else: ?>
                        <h1>Recent Notes:</h1>
<?php endif; ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Class</th>
                                    <th>Unit</th>
                                    <th>Notes Title</th>
                                    <th>Date Created</th>
                                </tr>
                            </thead>
                            <tbody>
<?php 
foreach ($notes as $index=>$note):
    $class = null;
    $unit = null;
    foreach($classes as $classCH) {
        if ($note["classId"] == $classCH["id"]) $class = $classCH;
    }
    foreach($units as $unitCH) {
        if ($note["unitId"] == $unitCH["id"]) $unit = $unitCH;
    }
?>
                                <tr>
                                    <td class="text-center"><?= $index+1 ?></td>
                                    <td><a class="btn-link" href="class.php?id=<?= $class["id"] ?>"><?= $class["name"] ?></a></td>
                                    <td><a class="btn-link" href="class.php?id=<?= $class["id"] ?>#unitHeading<?= $unit["unitNumber"] ?>">Unit <?= $unit["unitNumber"] ?></a></td>
                                    <td><a class="btn-link" href="note.php?id=<?= $note["id"] ?>"><?= $note["title"] ?></a></td>
                                    <td><?= date("g:i A l, F j\\t\\h Y", strtotime($note["dateCreated"])) ?></td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
<?php

// Include the footer
require "templates/footer.php";

?>