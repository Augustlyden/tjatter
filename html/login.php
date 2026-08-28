<?php
  require 'includes/formValidation.php';

  $email = $password = "";
  $emailErr = $passwordErr = "";

  if ($_SERVER["REQUEST_METHOD"]  === "POST") {
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
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tjatter - Login</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
</head>
<body>
  <div class="window">
    <div class="window-titlebar">
      <span class="pixel-bubble"></span>
      TJATTER // LOGIN
      <div class="window-controls">
        <span></span><span></span><span></span>
      </div>
    </div>
    
    <div class="window-body">
      <h1>Login</h1>
      <form method="post" action="login.php">
        <div>
          <label for="email"> <span class="required">*</span> Email address</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
          <span class="<?= $emailErr ? 'error' : '' ?>"><?= $emailErr ?></span>
        </div>
  
        <div>
          <label for="password"><span class="required">*</span> Password</label>
          <input type="password" id="password" name="password">
          <span class="<?= $passwordErr ? 'error' : '' ?>"><?= $passwordErr ?></span>
        </div>
  
        <button type="submit">Login</button>
      </form>
      <p class="window-footer">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </div>

</body>
</html>