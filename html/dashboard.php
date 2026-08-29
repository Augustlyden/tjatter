<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (isset($_SESSION['user_id'])) {
    $userStmt = $db->prepare(
      'SELECT 
        fname,
        lname,
        username
      FROM users
      WHERE id = :id;'
    );
    $userStmt->execute([':id' => $_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare(
      'SELECT 
        g.id AS group_id,
        g.group_name,
        g.subject,
        g.members
      FROM groups_ g
      JOIN group_members gm ON g.id = gm.group_id
      JOIN users u ON gm.user_id = u.id
      WHERE u.id = :id
      ORDER BY g.group_name;'
    );
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    header('Location: /login.php');
    die();
  }
?>

<?php 
  $page_name = 'Tjatter - Dashboard';
  include 'index.php'; 
?>
<body>
  <div class="page-layout">

    <?php include 'sidebar.php'; ?>

    <div class="window-group">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // DASHBOARD
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <div class="user-card halftone">
        <div class="avatar"></div>
        <div class="user-info">
          <span class="name"><?= $user['fname']?> <?= $user['lname'] ?></span>
          <span class="username">@<?= $user['username'] ?></span>
        </div>
      </div>

      <section class="group-grid">

        <?php if (empty($rows)): ?>  
          <div class="empty-rows">
            <p>You aren't part of any groups just yet.</p>
            <div class="empty-buttons">
              <a class="enter-button" href="groups.php">JOIN SOME?</a>
              <p>or</p>
              <a class="enter-button" href="create-group.php">CREATE YOUR OWN</a>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($rows as $row): ?>
            <article class="panel group-card">
              <span class="pixel-bubble"></span>
              <div class="group-card-body">
                <h2><?= htmlspecialchars($row['group_name']) ?></h2>
                <p class="group-subject"><?= htmlspecialchars($row['subject']) ?></p>
                <p class="group-members"><?= (int)$row['members'] ?> members</p>
                <div class="enter">
                  <a class="enter-button" href="group.php?id=<?= (int)$row['group_id'] ?>">Enter Group</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>

  </div>

</body>
</html>