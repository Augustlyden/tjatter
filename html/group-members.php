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
        gm.role,
        u.username
      FROM group_members gm
      JOIN users u ON gm.user_id = u.id
      WHERE gm.group_id = :group_id AND status = :status;'
    );
    $stmt->execute([
      ':group_id' => $groupId,
      ':status' => 'approved'
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    error_log('group-members.php:' . $e->getMessage());
    redirectAndKill('Something went wrong, try again', '/group.php?group-id=' . $groupId);
  }
?>

<?php 
  $page_name = 'Tjatter - Members';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-dashboard">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // MEMBERS
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>
      <section class="window-body">
        <?php foreach ($rows as $row): ?>
          <article class="request-card">
            <span class="name">@<?= htmlspecialchars($row['username']) ?></span>
            <form action="/change-role.php" method="post">
              <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
              <input type="hidden" name="group_id" value="<?= $groupId ?>">
              <select name="role" id="select-role">
                <option value="member" <?= $row['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
              </select>
              <button type="submit" class="select-button">CHANGE</button>
            </form>
          </article>
        <?php endforeach; ?>
      </section>
    </div>
  </div>
</body>