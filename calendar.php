<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

// Setup header
$header = new Header();
$header->active = 2;

date_default_timezone_set("America/New_York");

// Setup dates
$date = new DateTime();

$year = $date->format("Y");
$month = $date->format("n");

$deadlineId = null;

if (isset($_GET["year"]) && !empty($_GET["year"])) {
	$year = $_GET["year"];
}

if (isset($_GET["month"]) && !empty($_GET["month"])) {
	$month = $_GET["month"];
}

if (isset($_GET["deadline"]) && !empty($_GET["deadline"])) {
	$deadlineId = $_GET["deadline"];
}

$date->setDate($year, $month, 1);

$endDate = clone $date;
$endDate->setDate($endDate->format("Y"), $endDate->format("n"), $endDate->format("t"));

if (isset($_POST["toggleDeadline"]) && !empty($_POST["toggleDeadline"])) {
	$deadline = getData("SELECT * FROM `deadlines` WHERE userId='".$user['id']."' AND id='".mysqli_real_escape_string($db, $_POST["toggleDeadline"])."'");
	if (count($deadline) > 0) {
		$deadline = $deadline[0];
		$newDone = 0;
		if (!$deadline["done"]) $newDone = 1;
		setData("UPDATE `deadlines` SET done='$newDone' WHERE userId='".$user['id']."' AND id='{$deadline["id"]}'");
	}
}

if (isset($_POST["deleteDeadline"]) && !empty($_POST["deleteDeadline"])) {
	$deadline = getData("SELECT * FROM `deadlines` WHERE userId='".$user['id']."' AND id='".mysqli_real_escape_string($db, $_POST["deleteDeadline"])."'");
	if (count($deadline) > 0) {
		$deadline = $deadline[0];
		setData("DELETE FROM `deadlines` WHERE userId='".$user['id']."' AND id='{$deadline["id"]}'");
	}
}

if (isset($_POST["deleteReoccuringDeadline"]) && !empty($_POST["deleteReoccuringDeadline"])) {
	$deadline = getData("SELECT * FROM `reoccurringDeadlines` WHERE userId='".$user['id']."' AND id='".mysqli_real_escape_string($db, $_POST["deleteReoccuringDeadline"])."'");
	if (count($deadline) > 0) {
		$deadline = $deadline[0];
		setData("DELETE FROM `deadlines` WHERE userId='".$user['id']."' AND reoccurringDeadlineId='{$deadline["id"]}'");
		setData("DELETE FROM `reoccurringDeadlines` WHERE userId='".$user['id']."' AND id='{$deadline["id"]}'");
	}
}

$deadlines = getData("SELECT * FROM `deadlines` WHERE userId='".$user['id']."' AND deadline BETWEEN '".$date->format("Y-m-d 00:00:00")."' AND '".$endDate->format("Y-m-d 23:59:59")."' ORDER BY deadline ASC");

foreach ($deadlines as $key=>$deadline) {
	$deadlines[$key]["deadline"] = new DateTime($deadline["deadline"]);
	$deadlines[$key]["summary"] = "
		<h3 style='margin-top: 0;'>".htmlspecialchars($deadline['title'])." <i class='fa fa-".($deadline["done"] ? "check" : "times")."' aria-hidden='true'></i></h3>
		<small class='text-muted'>Due at ".(new DateTime($deadline['deadline']))->format("g:i A")."</small>
		<p>".htmlspecialchars($deadline['notes'])."</p>
		<a href='class.php?id=".$deadline["classId"]."' class='btn btn-success'>View Class</a>
		<form method='post' action='calendar.php?month={$month}&year={$year}&deadline={$deadline['id']}' style='margin: 0;'>
			<button type='submit' name='toggleDeadline' value='".$deadline["id"]."' class='btn btn-primary'>Mark as ".($deadline["done"] ? "Incomplete" : "Complete")."</button>
		</form>
		<form method='post' action='calendar.php?month={$month}&year={$year}'>
			<button type='submit' name='deleteDeadline' value='".$deadline["id"]."' class='btn btn-danger'>Delete Deadline</button>
		</form>
		<small class='text-muted'>Created on ".(new DateTime($deadline['dateCreated']))->format("F jS, Y")."</small>";
}

// Include the Header
require "templates/header.php";

?>
						<!-- Page Content Here -->
						<script>
							$(document).ready(function() {
								$('[data-toggle="popover"]').popover().on('click', function (e) {
									$('[data-toggle="popover"]').not(this).popover('hide');
								});
								$('[data-toggle="popover"].popover-show').popover('show');
							});
						</script>
						<div class="modal fade" tabindex="-1" role="dialog" id="modal" aria-labelledby="modalLabel" aria-hidden="true">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="modalLabel">Modal title</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<table class="table">
											<thead>
												<tr>
													<th class="text-center">#</th>
													<th>Title</th>
													<th>Class</th>
													<th>Time</th>
													<th>Days</th>
													<th>Start Date</th>
													<th>End Date</th>
													<th>(-)</th>
												</tr>
											</thead>
											<tbody>
<?php

$reoccurringDeadlines = getData("SELECT * FROM `reoccurringDeadlines` WHERE userId='".$user['id']."'");

$classes = [];

foreach($reoccurringDeadlines as $index=>$reoccurringDeadline) {
	$found = false;
	$classIndex = 0;
	foreach ($classes as $classId=>$class) {
		if ($class["id"] == $reoccurringDeadline["classId"]) {
			$found = true;
			$classIndex = $classId;
			break;
		}
	}
	if (!$found) {
		$class = getData("SELECT * FROM `classes` WHERE userId='".$user['id']."' AND id='".$reoccurringDeadline["classId"]."'");
		if (count($class) > 0) array_push($classes, $class[0]);
		$classIndex = count($classes) - 1;
	}
	$reoccurringDeadlines[$index]["class"] = $classes[$classIndex];
}

foreach ($reoccurringDeadlines as $index=>$reoccurringDeadline): ?>
												<tr>
													<td class="text-center"><?= ($index+1) ?></td>
													<td><?= $reoccurringDeadline["title"] ?></td>
													<td><a href="class.php?id=<?=$reoccurringDeadline["class"]["id"] ?>"><?= $reoccurringDeadline["class"]["name"] ?></a></td>
													<td><?= $reoccurringDeadline["time"] ?></td>
													<td>
														<i class='fa fa-<?= $reoccurringDeadline["mon"] ? "check" : "times" ?>' aria-hidden='true'></i>
														<i class='fa fa-<?= $reoccurringDeadline["tue"] ? "check" : "times" ?>' aria-hidden='true'></i>
														<i class='fa fa-<?= $reoccurringDeadline["wed"] ? "check" : "times" ?>' aria-hidden='true'></i>
														<i class='fa fa-<?= $reoccurringDeadline["thu"] ? "check" : "times" ?>' aria-hidden='true'></i>
														<i class='fa fa-<?= $reoccurringDeadline["fri"] ? "check" : "times" ?>' aria-hidden='true'></i>
														<i class='fa fa-<?= $reoccurringDeadline["sat"] ? "check" : "times" ?>' aria-hidden='true'></i>
														<i class='fa fa-<?= $reoccurringDeadline["sun"] ? "check" : "times" ?>' aria-hidden='true'></i>
													</td>
													<td><?= Date("n/j/y", strtotime($reoccurringDeadline["startDate"])) ?></td>
													<td><?= Date("n/j/y", strtotime($reoccurringDeadline["endDate"])) ?></td>
													<td class="text-center td-actions">
														<form method="post" action="calendar.php?month=<?= $month ?>&year=<?= $year ?>">
															<button type="submit" class="btn btn-danger btn-round" rel="tooltip" name="deleteReoccuringDeadline" value="<?= $reoccurringDeadline["id"] ?>">
																<i class="material-icons">remove</i>
															</button>
														</form>
													</td>
												</tr>
<?php endforeach; ?>
											</tbody>
										</table>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<button type="button" class="btn btn-primary btn-round" data-toggle="modal" data-target="#modal">Manage Reoccurring Deadlines</button>
							</div>
							<div class="col-md-9">
								<h2 class="text-right">
									<a href="calendar.php?month=<?= ($month-1) ?>&year=<?= $year ?>" class="btn btn-primary btn-round" style="vertical-align: top;">
										<i class="fa fa-caret-left" aria-hidden="true"></i>
									</a>
									<?= $date->format("F") ?>
									<a href="calendar.php?month=<?= ($month+1) ?>&year=<?= $year ?>" class="btn btn-primary btn-round" style="vertical-align: top;">
										<i class="fa fa-caret-right" aria-hidden="true"></i>
									</a>

									<a href="calendar.php?month=<?= $month ?>&year=<?= ($year-1) ?>" class="btn btn-info btn-round" style="vertical-align: top;">
										<i class="fa fa-caret-left" aria-hidden="true"></i>
									</a>
									<?= $date->format("Y") ?>
									<a href="calendar.php?month=<?= $month ?>&year=<?= ($year+1) ?>" class="btn btn-info btn-round" style="vertical-align: top;">
										<i class="fa fa-caret-right" aria-hidden="true"></i>
									</a>
								</h2>
							</div>
						</div>
						<table class="table">
							<thead>
								<tr>
<?php

$start = new DateTime('2020-01-01');
$interval = new DateInterval('P1M');
$end = new DateTime('2020-12-31');
$period = new DatePeriod($start, $interval, $end);

foreach ($period as $dt): ?>
									<th class="text-center">
										<a href="calendar.php?month=<?= $dt->format("n") ?>&year=<?= $year ?>" style="padding: 0; <?= $dt->format("M") == $date->format("M") ? "font-weight: bold;" : "" ?>" class="btn btn-link">
											<?= $dt->format("M") ?>

										</a>
									</th>
<?php endforeach; ?>
								</tr>
							</thead>
						</table>
						<table class="table">
							<thead>
								<tr>
									<th class="text-center" style="width: 14.285%;">Mon</th>
									<th class="text-center" style="width: 14.285%;">Tue</th>
									<th class="text-center" style="width: 14.285%;">Wed</th>
									<th class="text-center" style="width: 14.285%;">Thu</th>
									<th class="text-center" style="width: 14.285%;">Fri</th>
									<th class="text-center" style="width: 14.285%;">Sat</th>
									<th class="text-center" style="width: 14.285%;">Sun</th>
								</tr>
							</thead>
							<tbody>
								<tr>
<?php for ($i = 1; $i < $date->format("N"); $i++): ?>
									<td></td>
<?php endfor;

$interval = DateInterval::createFromDateString('1 day');
$endDate->setDate($endDate->format("Y"), $endDate->format("n")+1, 1);
$period = new DatePeriod($date, $interval, $endDate);

foreach ($period as $dt):
	if($dt->format("N") == 1): ?>
								</tr>
								<tr>
<?php endif; ?>
									<td class="text-center">
										<a href="#" onclick="event.preventDefault();" class="btn btn-round <?= $dt->format("j-n-Y") == Date("j-n-Y") ? "btn-success" : "btn-link" ?>"><?= $dt->format("j") ?></a>
<?php foreach ($deadlines as $deadline): if ($deadline["deadline"]->format("Y-m-d") == $dt->format("Y-m-d")): ?>
										<div style="width: 100%; margin: 1px 0; height: 10px; cursor: pointer; background-color: <?= $deadline["color"] ?>" <?= $deadlineId == $deadline["id"] ? 'class="popover-show"' : "" ?> tabindex="0" data-toggle="popover" data-placement="left" data-content="<?= $deadline["summary"] ?>" data-html="true"></div>
<?php endif; endforeach; ?>
									</td>
<?php endforeach; ?>
<?php if ($endDate->format("N") != 1) for ($i = $endDate->format("N")-1; $i < 7; $i++): ?>
									<td></td>
<?php endfor; ?>
								</tr>
							</tbody>
						</table>
<?php

// Include the footer
require "templates/footer.php";

?>