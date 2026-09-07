<?php
  require 'settings.php';
  require 'includes/functions.php';

  $email = $password = "";
  $emailErr = $passwordErr = $err = "";

  if ($_SERVER["REQUEST_METHOD"]  === "POST") {
    try {
      if (empty($_POST['email'])) {
        $emailErr = 'Email is required';
      } else {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailErr = "Invalid email format";
        }
      }
      if (empty($_POST['password'])) {
        $passwordErr = 'Password is required';
      } else {
        $password = $_POST["password"];
      }

      if (empty($emailErr) && empty($passwordErr) ) {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email;');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($password, $row['password'])) {
          $err = "Wrong email or password";
        }

        if (empty($err)) {
          $_SESSION['user_id'] = $row['id'];
          header('Location: /dashboard.php');
          die();
        }
      }
    } catch (Exception $e) {
      error_log('login.php:' . $e->getMessage());
      $err = 'Something went wrong, try again';
    }
  }
?>

<?php 
  $page_name = 'Tjatter - Login';
  include 'index.php';
?>
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
      <h1 class="title">Login</h1>
      <form class="form" method="post" action="login.php">
        <div>
          <label for="email"> <span class="required">*</span> Email address</label>
          <input class="input" type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
          <span class="<?= $emailErr ? 'error' : '' ?>"><?= $emailErr ?></span>
        </div>
  
        <div>
          <label for="password"><span class="required">*</span> Password</label>
          <input class="input" type="password" id="password" name="password">
          <span class="<?= $passwordErr ? 'error' : '' ?>"><?= $passwordErr ?></span>
        </div>
  
        <button class="button" type="submit">Login</button>
        <span class="<?= $err ? 'error' : '' ?>"><?= $err ?></span>
      </form>
      <p class="window-footer">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </div>
</body>
</html>