<?php
  require 'settings.php';
  require 'includes/formValidation.php';
  require 'includes/functions.php';

  $fname = $lname = $username = $email = $password = "";
  $fnameErr = $lnameErr = $usernameErr = $emailErr = $passwordErr = $err = $usernameExistsErr = "";

  if ($_SERVER["REQUEST_METHOD"]  === "POST") {
    if (empty($_POST['fname'])) {
      $fnameErr = 'Firstname is required';
    } else {
      $fname = test_input($_POST['fname']);
      if (!preg_match("/^[\p{L}' -]*$/u",$fname)) {
        $fnameErr = "Only letters and white space allowed";
      }
    }

    if (empty($_POST['lname'])) {
      $lnameErr = 'Lastname is required';
    } else {
      $lname = test_input($_POST['lname']);
      if (!preg_match("/^[\p{L}' -]*$/u",$lname)) {
        $lnameErr = "Only letters and white space allowed";
      }
    }
    
    if (empty($_POST['username'])) {
      $usernameErr = 'Username is required';
    } else {
      $username = test_input($_POST['username']);
    }

    if (empty($_POST['email'])) {
      $emailErr = 'Email is required';
    } else {
      $email = test_input($_POST['email']);
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
      }
    }
    
    if (empty($_POST['password'])) {
      $passwordErr = 'Password is required';
    } else {
      $password = $_POST["password"];
      if (strlen($password) < 8) {
        $passwordErr = 'Password must be at least 8 characters';
      }
    }

    if (empty($fnameErr) && empty($lnameErr) && empty($usernameErr) && empty($emailErr) && empty($passwordErr)) {
      $checkEmail = $db->prepare('SELECT * FROM users WHERE email = :email;');
      $checkEmail->execute([':email' => $email]);
      $row = $checkEmail->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $err = "Email address is already registred, do you want to login instead?";
      }

      $checkUsername = $db->prepare('SELECT * FROM users WHERE username = :username;');
      $checkUsername->execute([':username' => $username]);
      $row = $checkUsername->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $usernameExistsErr = "Username already exists, please choose another one";
      }

      if (empty($err) && empty($usernameExistsErr)) {
        $stmt = $db->prepare(
          'INSERT INTO users (fname, lname, username, email, password) 
          VALUES (:fname, :lname, :username, :email, :password)'
        );
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([
          ':fname' => $fname,
          ':lname' => $lname, 
          ':username' => $username, 
          ':email' => $email, 
          ':password' => $hashedPassword
        ]);
        $newUserId = $db->lastInsertId();
        $_SESSION['user_id'] = $newUserId;
        header('Location: /dashboard.php');
        die();
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tjatter - Register</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
</head>
<body>
  <div class="window">
    <div class="window-titlebar">
      <span class="pixel-bubble"></span>
      TJATTER // REGISTER
      <div class="window-controls">
        <span></span><span></span><span></span>
      </div>
    </div>

    <div class="window-body">
      <h1>Register</h1>
      <form method="post" action="register.php">
        <div>
          <label for="fname"><span class="required">*</span> Firstname</label>
          <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($fname) ?>">
          <span class="<?= $fnameErr ? 'error' : '' ?>"><?= $fnameErr ?></span>
        </div>

        <div>
          <label for="lname"><span class="required">*</span> Lastname</label>
          <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($lname) ?>">
          <span class="<?= $lnameErr ? 'error' : '' ?>"><?= $lnameErr ?></span>
        </div>

        <div>
          <label for="username"><span class="required">*</span> Username</label>
          <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>">
          <span class="<?= $usernameErr ? 'error' : '' ?>"><?= $usernameErr ?></span>
        </div>
  
        <div>
          <label for="email"><span class="required">*</span> Email address</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
          <span class="<?= $emailErr ? 'error' : '' ?>"><?= $emailErr ?></span>
        </div>

        <div>
          <label for="password"><span class="required">*</span> Password</label>
          <input type="password" id="password" name="password">
          <span class="<?= $passwordErr ? 'error' : '' ?>"><?= $passwordErr ?></span>
        </div>
  
        <button type="submit">Register</button>
        <span class="<?= $err ? 'error' : '' ?>"><?= $err ?></span>
        <span class="<?= $usernameExistsErr ? 'error' : '' ?>"><?= $usernameExistsErr ?></span>
      </form>
  
      <p class="window-footer">Already have an account? <a href="login.php">Login</a></p>
    </div>

  </div>
</body>
</html>