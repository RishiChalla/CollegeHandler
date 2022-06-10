<?php

// Include Config files
require "config/config.php";
require "config/userInfo.php";

// Setup contact form
$form = new Form("POST", [
	new FormEmailInput("Email Address", "email", "The email address to respond to", "email"),
	new FormTextInput("Subject", "", "title", "Subject - What can we help you with?", "subject"),
	new FormInput("Message", "", "", "Message Body", "message", "", false)
], "contactBtn");

$errors = $form->updateData();

$waitList = true;

// Setup header
$header = new Header();
$header->active = 8;

// Include the Header
require "templates/header.php";

?>
						<!-- Page Content Here -->
						<h1>Contact Us</h1>
<?php if($waitList): ?>
						<h3>Note: Any sent messages will be added to a waitlist currently.</h3>
<?php endif; ?>
<?php

if (gettype($errors) == "array") {
	if (count($errors) > 0) {

?>
<?php foreach ($errors as $error): ?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<strong>Error: </strong> <?= $error ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
<?php endforeach; ?>
<?php

	}
	else {
		$result = $form->process('INSERT INTO `contacts` (email, subject, message, blocked) VALUES (${email}, ${subject}, ${message}, \''.($waitList?"1":"0").'\')');

?>
<?php if ($result): ?>
						<div class="alert alert-<?= $waitList ? "warning" : "success" ?> alert-dismissible fade show" role="alert">
							<strong><?= $waitList ? "Warning" : "Success" ?></strong>: <?= $waitList ? "Your message was not sent and was put on a waitlist." : "Your message was successfully sent." ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
<?php else: ?>
<?php endif; ?>

<?php

	}
}

?>
						<form method="post" action="contact.php">
<?php

$form->createForm('
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="material-icons">${icon}</i>
									</span>
								</div>
								<input type="${type}" name="${name}" class="form-control" placeholder="${description}">
							</div>
');

?>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="material-icons">message</i>
									</span>
								</div>
								<textarea style="height: 20vh !important;" name="message" class="form-control" placeholder="Message Body"></textarea>
							</div>
							<input type="submit" name="contactBtn" class="btn btn-primary btn-block" value="Send Message">
						</form>
<?php

// Include the footer
require "templates/footer.php";

?>