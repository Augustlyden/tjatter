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
      <form>
        <div>
          <label for="fname">Firstname</label>
          <input type="text" id="fname" name="fname">
        </div>

        <div>
          <label for="username">Lastname</label>
          <input type="text" id="lname" name="lname">
        </div>

        <div>
          <label for="username">Username</label>
          <input type="text" id="username" name="username">
        </div>
  
        <div>
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" required>
        </div>

        <div>
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
  
        <button type="submit">Register</button>
      </form>
  
      <p class="window-footer">Already have an account? <a href="login.php">Login</a></p>
    </div>


  </div>
</body>
</html>