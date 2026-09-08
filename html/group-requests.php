<?php
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  if (!isset($_GET['group_id'])) {
    redirectAndKill('Invalid request', '/dashboard.php');
  }

  $groupId = (int)$_GET['group_id'];
  $err = "";

  try {
    $adminCheck = $db->prepare(
      'SELECT * FROM users u
      JOIN group_members gm ON gm.user_id = u.id
      WHERE u.id = :user_id AND gm.group_id = :group_id;'
    );
    $adminCheck->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $groupId
    ]);
    $admin = $adminCheck->fetch(PDO::FETCH_ASSOC);
  
    if (!$admin || $admin['role'] !== 'admin') {
      redirectAndKill("You don't have access to this site", '/dashboard.php');
    }

    $stmt = $db->prepare(
      'SELECT 
        gm.user_id,
        u.username 
      FROM group_members gm
      JOIN users u ON gm.user_id = u.id
      WHERE gm.group_id = :group_id AND status = :status;'
    );
    $stmt->execute([
      ':group_id' => $groupId,
      ':status' => 'pending'
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    error_log('group-requests.php:' . $e->getMessage());
    $err = 'Something went wrong, try again.';
  }
?>

<?php 
  $page_name = 'Tjatter - Requests';
  include 'index.php';
?>

<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-dashboard">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // REQUESTS
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>
      <section class="window-body post-section">
        <?php if (isset($_SESSION['message'])): ?>
          <p class="error"><?= $_SESSION['message'] ?></p>
          <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (!empty($err)): ?>
          <span class="error"><?= $err ?></span>
        <?php elseif (empty($rows)): ?>
          <div class="empty-rows">
            <p>No pending requests.</p>
          </div>
        <?php else: ?>
          <?php foreach ($rows as $row): ?>
            <article class="request-card">
              <span class="name">@<?= htmlspecialchars($row['username']) ?></span>
              <form action="/request-accept.php" method="post">
                <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                <input type="hidden" name="group_id" value="<?= $groupId ?>">
                <button type="submit" class="button">Accept</button>
              </form>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>
  </div>
</body>
</html>