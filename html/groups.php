<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare(
      'SELECT 
        g.id AS group_id,
        g.group_name as name,
        g.subject,
        g.members
      FROM groups_ g
      WHERE NOT EXISTS (
        SELECT 1 FROM group_members gm
        WHERE gm.group_id = g.id AND gm.user_id = :id
      )
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
  $page_name = 'Tjatter - All Groups';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-group">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // ALL GROUPS
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>
      <section class="group-grid">
        <?php if (empty($rows)): ?>  
          <div class="empty-rows">
            <p>Oops, no available groups here.</p>
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
                <p class="group-members"><?= (int)$row['members'] ?> members</p>
                <form method="post" action="request-join.php">
                  <input type="hidden" name="group_id" value="<?= (int)$row['group_id'] ?>">
                  <button type="submit">Request to join</button>
                  <?php if (isset($_SESSION['err'])); ?>
                    <p><?= $_SESSION['err'] ?></p>
                    <?php unset($_SESSION['err']); ?>
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