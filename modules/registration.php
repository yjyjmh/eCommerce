<?php

require_once("config.php");

try {
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB, USER, PW);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

/* Get the variables from the registration form */
$name = $_POST['name'];
$surname = $_POST['surname'];
$username = $_POST['username'];
$password = $_POST['password'];
$country = $_POST['country'];
$address = $_POST['address'];
$email = $_POST['email'];

/* Check section parameters */
$text_match = "/^[a-zA-Z][a-z]*/";
$email_match = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/";

$error_flag = false;

include("static/header.html");

?>
	<div id="center">
		<div id="navigation">
			<div id="pagenav">
			<?php
			/* Check the registration form parameteres */
			if($name == '' || $surname == '' || $username == '' || $password == '' || $address == '' || $email == '') {
				echo "Error: one or more form fields are empty!";
				$error_flag = true; ?>
				<br><br>
				<a href="static/register.html">Go back to the registration form</a>			
			<?php }

			if($error_flag == false && ( !preg_match($text_match, $name) || !preg_match($text_match, $surname) || !preg_match($text_match, $username) || !preg_match($text_match, $country) || !preg_match($email_match, $email) )) {
				echo "Error: one or more form fields are in not valid format!";
				$error_flag = true; ?>
                                <br><br>
                                <a href="static/register.html">Go back to the registration form</a>
			<?php }

			if($error_flag == false && ( strlen($password) < 6 || strlen($password) > 15 )) {
				echo "The password must be at least 6 characters (max 15 characters)!";
				$error_flag = true; ?>
                                <br><br>
                                <a href="static/register.html">Go back to the registration form</a>
			<?php } 
			
			if($error_flag == false) {
				/* Check if the username already exists */
				$stmt = $pdo->prepare("SELECT username FROM users WHERE username = :username LIMIT 1");
				$stmt->bindParam(':username', $username, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() != 0) { 
					echo "The username $username is already in use. Please choose another username";
					$error_flag = true; ?>
					<br><br>
					<a href="static/register.html">Go back to the registration form</a>
				<?php }
			}
			
			if($error_flag == false) {
				/* Insert the user data in the database */
				$stmt = $pdo->prepare("INSERT INTO users (name, surname, country, address, password, email, username) VALUES (:name, :surname, :country, :address, :password, :email, :username)");
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
				$stmt->bindParam(':country', $country, PDO::PARAM_STR);
				$stmt->bindParam(':address', $address, PDO::PARAM_STR);
				$stmt->bindParam(':password', $password, PDO::PARAM_STR);
				$stmt->bindParam(':email', $email, PDO::PARAM_STR);
				$stmt->bindParam(':username', $username, PDO::PARAM_STR);
				$stmt->execute();

				/* Registration Successful */
				echo "Registration successful!"; ?>
				<br><br>
				Please <a href="../index.php">Login</a> to access the store.
 			<?php
			}
			?>
			</div>
		</div>
	</div>
<?php

include("static/footer.html");

?>
