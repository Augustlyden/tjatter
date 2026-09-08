<?php
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  if (!isset($_GET['token'])) {
    redirectAndKill('Invalid request', '/dashboard.php');
  }

  $token = $_GET['token'];
  $err = "";
  $baseUrl = $_SERVER['HTTP_HOST'];

  try {
    
    $stmt = $db->prepare(
      'SELECT * FROM invites
      WHERE token = :token;'
    );
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      redirectAndKill("Could't find what you were looking for.", '/dashboard.php');
    }

    $adminCheck = $db->prepare(
      'SELECT * FROM users u
      JOIN group_members gm ON gm.user_id = u.id
      WHERE u.id = :user_id AND gm.group_id = :group_id;'
    );
    $adminCheck->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $row['group_id']
    ]);
    $admin = $adminCheck->fetch(PDO::FETCH_ASSOC);
  
    if (!$admin || $admin['role'] !== 'admin') {
      redirectAndKill("You don't have access to this site", '/dashboard.php');
    }
  } catch (Exception $e) {
    error_log('show-invite.php:' . $e->getMessage());
    $err = 'Something went wrong, try again.';
  }
?>

<?php 
  $page_name = 'Tjatter - Invite Link';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-dashboard">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // INVITE LINK
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <div>
        <?php if (!empty($err)): ?>
          <span class="error"><?= $err ?></span>
        <?php else: ?>
          <section class="invite-link">
            <p class="label">Copy the link and send it to someone you like. The invite is a onetime use and is valid for 24 hours.</p>
            <input class="input" type="text" readonly value="http://<?= htmlspecialchars($baseUrl . '/invite.php?token=' . $row['token'])?> " onclick="this.select()">
          </section>
        <?php endif ?>
      </div>
    </div>
  </div>
</body>
</html>