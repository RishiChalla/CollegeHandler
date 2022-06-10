<?php

// Make sure header info is given
if (!isset($header)) die("Please define \$header as type Header.");

// Set title if it isn't already given
if (!isset($header->title)) $header->title = "College Handler";

// Ensure that active menu item is given
if (!isset($header->active)) die("Please define \$active as an integer representing a selected navbar item.");

// Set sub-title if it isn't already given
if (!isset($header->subTitle)) $header->subTitle = $nav[$header->active]["text"];

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php echo $header->title; ?></title>
		<style>
		    body.dark-edition, body.dark-edition * {
		        color: white;
		    }
		    
		    body.dark-edition .modal-dialog * {
                color: rgb(60, 72, 88);
            }
		</style>
		<script>
			// Stop form resubmission on reload
			if (window.history.replaceState) {
				window.history.replaceState(null, null, window.location.href);
			}

			window.toggleLightMode = function() {
				document.body.classList.toggle("dark-edition");
				document.body.classList.toggle("light-edition");
				window.c = document.getElementById("mainSidebarDarkModeToggle").getAttribute("data-background-color");
				if (c == "white") c = "black";
				else if (c == "black") c = "white";
				document.getElementById("mainSidebarDarkModeToggle").setAttribute("data-background-color", c);
				document.getElementById("toggleLightModeBtn").innerText = `Switch to ${c == "white" ? "Dark" : "Light"} Mode`;

				$.ajax({
					type: "POST",
					url: "actions/toggleDarkMode.php",
					dataType: "text",
					data: {
						"apiKey": "<?= $user["apiKey"] ?>"
					}
				});
			};
		</script>
<?php

// Load css and js
foreach ($css as $src) {
	echo "\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"$src\">\n";
}

foreach ($js as $src) {
	echo "\t\t<script type=\"text/javascript\" src=\"$src\"></script>\n";
}

?>
	</head>
	<?php if ($user["darkMode"] == 1): ?>
	<body class="dark-edition">
	<?php else: ?>
	<body>
	<?php endif ?>
		<div class="wrapper">
			<!-- Side Bar -->
			<div class="sidebar" id="mainSidebarDarkModeToggle" data-color="green" data-background-color="<?= $user["darkMode"] == 1 ? "black" : "white" ?>" data-image="<?= $WEBSITE_URL ?>material-dashboard-master/assets/img/sidebar-4.jpg">
				<div class="logo">
					<a href="<?= $WEBSITE_URL ?>" class="simple-text logo-normal">
						College Handler
					</a>
				</div>
				<div class="sidebar-wrapper">
					<ul class="nav">
<?php
	$i = 0;
	foreach ($nav as $navItem) {
		echo "\t\t\t\t\t\t<li class=\"nav-item ".($i == $header->active ? "active" : "")."\">
\t\t\t\t\t\t\t<a class=\"nav-link\" href=\"$navItem[link]\">
\t\t\t\t\t\t\t\t<i class=\"material-icons\">$navItem[icon]</i>
\t\t\t\t\t\t\t\t<p>$navItem[text]</p>
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>\n";
		$i++;
	}
?>
						<li class="nav-item active-pro">
							<a class="nav-link" href="#">
								<i class="material-icons">thumb_up</i>
								<p>You Will Succeed!</p>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<!-- Main Panel -->
			<div class="main-panel">
				<!-- Top bar -->
				<nav id="mainNav" class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
					<div class="container-fluid">
						<div class="navbar-wrapper">
							<a class="navbar-brand" href="javascript:;"><?= $header->subTitle ?></a>
						</div>
						<button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
							<span class="sr-only">Toggle navigation</span>
							<span class="navbar-toggler-icon icon-bar"></span>
							<span class="navbar-toggler-icon icon-bar"></span>
							<span class="navbar-toggler-icon icon-bar"></span>
						</button>
						<div class="collapse navbar-collapse justify-content-end">
<?php if ($header->search): ?>
							<!-- Search Bar -->
							<form class="navbar-form" method="get" action="<?= $WEBSITE_URL.$header->searchAction ?>">
								<div class="input-group no-border">
									<input type="search" name="search" class="form-control" placeholder="Search...">
									<button type="submit" class="btn btn-white btn-round btn-just-icon">
										<i class="material-icons">search</i>
										<div class="ripple-container"></div>
									</button>
								</div>
							</form>
<?php endif; ?>
							<ul class="navbar-nav">
<?php

$query = "SELECT * FROM `deadlines` WHERE userId = {$user['id']} AND done = 0 AND `deadline` > NOW() ORDER BY deadline ASC LIMIT 20";
$result = mysqli_query($db, $query);
if(!$result) {
	errorEmails(
		"Error In Your Website (College Handler)",
		"
There was an error in your website (".$WEBSITE_URL.") while trying to load deadlines for a user. Here are the details:
User Email: ".$user["email"]."
SQL Attempted to Run: $query

Mysqli Error report:

".mysqli_error($db)
	);
	die("We appear to be having technical difficulties, please try again later!");
}

?>
								<!-- Notifications -->
<?= $header->additionalNav; ?>
								<li class="nav-item dropdown">
									<a class="nav-link" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="material-icons">notifications</i>
<?php if (mysqli_num_rows($result) > 0): ?>
										<span class="notification"><?= mysqli_num_rows($result) ?></span>
<?php endif; ?>
										<p class="d-lg-none d-md-block">
											Nearby Deadlines
										</p>
									</a>
									<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
<?php

while ($row = mysqli_fetch_assoc($result)) {
	$deadlineDate = new DateTime($row["deadline"]);
	echo "\t\t\t\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"{$WEBSITE_URL}calendar.php?month=".$deadlineDate->format("n")."&year=".$deadlineDate->format("Y")."&deadline=".$row["id"]."\">
	\t\t\t\t\t\t\t\t\t\t{$row['title']}
	\t\t\t\t\t\t\t\t\t\t<sub class=\"text-muted\" style=\"margin-left:10pt;\">".$deadlineDate->format("F jS, Y")."</sub>
	\t\t\t\t\t\t\t\t\t</a>\n";
}

if (mysqli_num_rows($result) == 0) {
	echo "\t\t\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Nothing Due Soon!</a>";
}

?>
									</div>
								</li>
								<li class="nav-item dropdown">
									<a class="nav-link" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="fa fa-user" aria-hidden="true"></i>
										<p class="d-lg-none d-md-block">
											<?= $user["name"] ?>
										</p>
									</a>
									<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
										<a class="dropdown-item" href="#"><?= $user["name"] ?></a>
										<a class="dropdown-item" href="<?= $WEBSITE_URL ?>logout.php">Logout</a>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item" href="#" id="toggleLightModeBtn" onclick="toggleLightMode();">Switch to <?= $user["darkMode"] == 1 ? "Light" : "Dark" ?> Mode</a>
									</div>
								</li>
							</ul>
						</div>
					</div>
				</nav>
				<div class="content">
<?php if ($header->containerFluid): ?>
					<div class="container-fluid">
<?php endif; ?>
