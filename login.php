<?php

// Include config files
require "config/config.php";

if (isset($_SESSION["userId"]) && !empty($_SESSION["userId"])) {
	header("Location: index.php");
	exit();
}

// Login form
$loginForm = new Form("POST", [
	new FormEmailInput("Email Address", "envelope", "The email address your account is associated with", "email"),
	new FormPasswordInput("Password", "lock", "The password your account is associated with", "password")
], "login");

// Signup form
$signupForm = new Form("POST", [
	new FormEmailInput("Email Address", "envelope", "The email address your account is associated with", "email"),
	new FormTextInput("Username", "", "font", "The username of your new account (only you will see this).", "username", 5, 100),
	new FormPasswordInput("Password", "lock", "The password your account is associated with", "password"),
	new FormPasswordInput("Repeat Password", "lock", "Type the password again to ensure there are no typos", "rPassword"),
], "signup");

$loginErrors = [];
$signupErrors = [];
$signupSuccess = false;

// Login form validation
if (isset($_POST[$loginForm->submit])) {
	$errors = $loginForm->updateData();

	foreach ($errors as $error) {
		array_push($loginErrors, $error);
	}

	if (count($errors) == 0) {
		$user = getData($loginForm->sql('SELECT * FROM `users` WHERE email=${email}'));
		if (count($user) > 0) {
			$user = $user[0];
			if (password_verify($loginForm->getRawValue("password"), $user["password"])) {
				$_SESSION["userId"] = $user["id"];
				header("Location: index.php");
				exit();
			}
			else {
				array_push($loginErrors, "The password you have entered is incorrect.");
			}
		}
		else {
			array_push($loginErrors, "We were unable to find a user associated with the email you have given us.");
		}
	}
}

// Signup form validation
if (isset($_POST[$signupForm->submit])) {

	$errors = $signupForm->updateData();

	if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) {
		array_push($errors, "Please fill out the ReCaptcha to prove you are not a bot!");
	}
	else {
		$captcha =  $_POST["g-recaptcha-response"];
		$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($CAPTCHA_KEYS["server"]).'&response='.urlencode($captcha);
		$response = file_get_contents($url);
		$responseKeys = json_decode($response, true);
		if (!$responseKeys["success"]) {
			array_push($errors, "Why are you trying to spam our site? Please just leave. Your IP has been blocked.");
		}
	}

	if ($signupForm->getRawValue("password") != $signupForm->getRawValue("rPassword")) {
		array_push($errors, "Please make sure that your password and repeat password match.");
	}

	foreach ($errors as $error) {
		array_push($signupErrors, $error);
	}

	if (count($errors) == 0) {
		$user = getData($signupForm->sql('SELECT * FROM `users` WHERE email=${email}'));
		if (count($user) > 0) {
			array_push($signupErrors, "Someone else already has an account with that email address. If you own this email, reset your password.");
		}
		else {
			setData($signupForm->sql('INSERT INTO `users` (email, name, password, lastLoggedIn, apiKey)
				VALUES (${email}, ${username}, ${password}, \''.Date("Y-m-d H:i:s").'\', \''.uniqid().'\')'));
			$signupSuccess = true;
		}
	}
}

array_push($css, "css/login.css");

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>College Handler</title>
<?php foreach ($css as $src): ?>
		<link rel="stylesheet" type="text/css" href="<?= $src ?>">
<?php endforeach; foreach ($js as $src): ?>
		<script type="text/javascript" src="<?= $src ?>"></script>
<?php endforeach; ?>
		<script>
			// Stop form resubmission on reload
			if (window.history.replaceState) {
				window.history.replaceState(null, null, window.location.href);
			}
		</script>
	</head>
	<body>
		<div class="container">
			<h1 style="text-align: center; margin: 7vh 0;">College Handler</h1>
			<div class="row">
				<div class="col-md-6">
					<div class="card">
						<div class="card-header card-header-primary">
							<h2 class="card-title">Signup</h2>
							<h4 class="category">Signup for a simple and free note-taking system</h4>
						</div>
						<div class="card-body">
							<form method="post" action="login.php">
						<?php

$signupForm->createForm('
								<div class="form-group">
									<label>${title}</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fa fa-${icon}"></i>
											</span>
										</div>
										<input type="${type}" name="${name}" class="form-control" placeholder="${description}">
									</div>
								</div>');


?>
								<div class="g-recaptcha" data-sitekey="<?= $CAPTCHA_KEYS["client"] ?>"></div>
								<br>
								<button type="submit" name="signup" class="btn btn-success">Signup</button>
<?php

if (isset($_POST[$signupForm->submit])) {
	foreach ($signupErrors as $error) {
		echo alert("danger", $error);
	}
}

if ($signupSuccess) {
	echo alert("success", "We have successfully signed you up! You may now login with your information");
}

?>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-header card-header-success">
							<h2 class="card-title">Login</h2>
							<h4 class="category">Login to your account</h4>
						</div>
						<div class="card-body">
							<form method="post" action="login.php">
<?php

$loginForm->createForm('
								<div class="form-group">
									<label>${title}</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fa fa-${icon}"></i>
											</span>
										</div>
										<input type="${type}" name="${name}" class="form-control" placeholder="${description}">
									</div>
								</div>');


?>
								<button type="submit" name="login" class="btn btn-primary">Login</button>
								<button type="button" class="btn btn-danger">Reset Password</button>
<?php

if (isset($_POST[$loginForm->submit])) {
	foreach ($loginErrors as $error) {
		echo alert("danger", $error);
	}
}

?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<footer class="footer">
			<div class="container">
				<a href="privacy.php" style="margin: 0;" class="btn btn-link">Privacy Policy</a>
				<a href="terms.php" style="margin: 0;" class="btn btn-link">Terms and Conditions</a>
			</div>
		</footer>
	</body>
</html>