<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
header("Clear-Site-Data: \"cache\", \"storage\"");

if (isset($_SESSION['user_id'])) {
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
	session_start();
}

$loginError = '';
$registerError = '';
$registerSuccess = '';
$showRegister = false;

if (isset($_SESSION['login_error'])) {
	$loginError = $_SESSION['login_error'];
	unset($_SESSION['login_error']);
	$showRegister = false;
}

if (isset($_SESSION['register_success'])) {
	$registerSuccess = $_SESSION['register_success'];
	unset($_SESSION['register_success']);
	$showRegister = true;
}

if (isset($_SESSION['register_error'])) {
	$registerError = $_SESSION['register_error'];
	unset($_SESSION['register_error']);
	$showRegister = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Login Page</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<section>
		<div class="leaves">
			<div class="set">
				<div><img src="leaf_01.png"></div>
				<div><img src="leaf_02.png"></div>
				<div><img src="leaf_03.png"></div>
				<div><img src="leaf_04.png"></div>
				<div><img src="leaf_01.png"></div>
				<div><img src="leaf_02.png"></div>
				<div><img src="leaf_03.png"></div>
				<div><img src="leaf_04.png"></div>
			</div>
		</div>
		<img src="bg.jpg" class="bg">
		<img src="trees.png" class="trees">

		<div class="auth-wrapper">
			<form class="auth-card login-card<?= $showRegister ? ' hidden' : '' ?>" id="login-box" action="login.php" method="POST">
				<h2>Sign In</h2>
				<div class="field-group">
					<input type="email" name="email" placeholder="Email" required>
				</div>
				<div class="field-group">
					<input type="password" name="password" placeholder="Password" required>
				</div>
				<div class="field-group">
					<input type="submit" value="Login" id="login-submit">
				</div>
				<?php if (!empty($loginError)): ?>
					<div class="form-message error"><?= htmlspecialchars($loginError) ?></div>
				<?php endif; ?>
				<div class="form-links" style="justify-content: flex-end;">
					<a href="#" onclick="showForm('register'); return false;">Register</a>
				</div>
			</form>

			<form class="auth-card register-card<?= $showRegister ? '' : ' hidden' ?>" id="register-box" action="register.php" method="POST">
				<h2>Sign Up</h2>
				<div class="field-group">
					<input type="text" name="name" placeholder="Full Name" required>
				</div>
				<div class="field-group">
					<input type="email" name="email" placeholder="Email" required>
				</div>
				<div class="field-group">
					<input type="password" name="password" placeholder="Password" required>
				</div>
				<div class="field-group">
					<input type="text" name="phone" placeholder="Phone Number" required>
				</div>
				<div class="field-group">
					<select name="gender" required>
						<option value="" disabled selected>--select gender--</option>
						<option value="male">Male</option>
						<option value="female">Female</option>
						<option value="other">Other</option>
					</select>
				</div>
				<div class="field-group">
					<select name="role" required>
						<option value="">--select role--</option>
						<option value="user">User</option>
						<option value="admin">Admin</option>
					</select>
				</div>
				<div class="field-group">
					<input type="submit" value="Register" id="register-submit">
				</div>
				<?php if (!empty($registerSuccess)): ?>
					<div class="form-message success"><?= htmlspecialchars($registerSuccess) ?></div>
				<?php endif; ?>
				<?php if (!empty($registerError)): ?>
					<div class="form-message error"><?= htmlspecialchars($registerError) ?></div>
				<?php endif; ?>
				<div class="form-links">
					<a href="#" onclick="showForm('login'); return false;">Already have an account? Sign In</a>
				</div>
			</form>
		</div>
	</section>

	<script src="script.js"></script>
</body>

</html>