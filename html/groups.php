<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  $err = "";

  try {
    $stmt = $db->prepare(
      'SELECT 
        g.id AS group_id,
        g.group_name as name,
        g.subject,
        g.members,
        gm.status
      FROM groups_ g
      LEFT JOIN group_members gm ON gm.group_id = g.id AND gm.user_id = :id
      WHERE gm.status IS NULL OR gm.status = \'pending\'
      ORDER BY g.group_name;'
    );
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    error_log('groups.php:' . $e->getMessage());
    $err = 'Something went wrong, try again.';
  }
?>

<?php 
  $page_name = 'Tjatter - All Groups';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-dashboard">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // ALL GROUPS
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>
      <section class="group-grid">
        <?php if (!empty($err)): ?>
          <span class="error"><?= $err ?></span>
        <?php elseif (empty($rows)): ?>  
          <div class="empty-rows">
            <p>Oops, you've joined all groups already.</p>
            <div class="empty-buttons">
              <a class="enter-button" href="create-group.php">CREATE ONE?</a>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($rows as $row): ?>
            <article class="panel group-card">
              <span class="pixel-bubble"></span>
              <div class="group-card-body">
                <h2><?= htmlspecialchars($row['name']) ?></h2>
                <p class="group-subject"><?= htmlspecialchars($row['subject']) ?></p>
                <p class="group-members">
                  <?= (int)$row['members'] ?> 
                  <?php $members = (int)$row['members'] === 1 ? 'member' : 'members'?>
                  <?= $members ?>
                </p>
                <form method="post" action="request-join.php">
                  <input type="hidden" name="group_id" value="<?= (int)$row['group_id'] ?>">
                  <button 
                    <?= $row['status'] === 'pending' ? 'disabled' : '' ?>
                    class="<?= $row['status'] === 'pending' ? 'button-disabled' : 'button' ?>" 
                    type="submit"
                  >
                    <?= $row['status'] === 'pending' ? 'Request Pending' : 'Request to join' ?>
                  </button>
                  <?php if (isset($_SESSION['message'])): ?>
                    <p class="error"><?= $_SESSION['message'] ?></p>
                    <?php unset($_SESSION['message']); ?>
                  <?php endif; ?>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>
  </div>
</body>
</html>