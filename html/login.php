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
      <form>
        <div>
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" required>
        </div>
  
        <div>
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
  
        <button type="submit">Login</button>
      </form>
      <p class="window-footer">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </div>

</body>
</html>