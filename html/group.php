<?php 
  require 'settings.php';
  require 'includes/functions.php';

  $err = "";
  $group_id = (int)$_GET['id'];

  if (isset($_SESSION['user_id'])) {
    $groupCheck = $db->prepare(
      'SELECT * FROM groups_ WHERE id = :id;'
    );
    $groupCheck->execute([':id' => $group_id]);
    $group = $groupCheck->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
      redirectAndKill('This "group" does not exist.');
    }

    $stmt = $db->prepare(
      'SELECT 
        g.id AS group_id,
        g.group_name as name,
        g.subject,
        g.members,
        g.description
      FROM groups_ g
      JOIN group_members gm ON gm.group_id = g.id
      JOIN users u ON gm.user_id = u.id 
      WHERE g.id = :group_id AND u.id = :user_id AND gm.status = \'approved\';'
    );
    $stmt->execute([':group_id' => $group_id, ':user_id' => $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    header('Location: /login.php');
    die();
  }
?>

<?php 
  $page_name = 'Tjatter -' . htmlspecialchars($group['group_name']);
  include 'index.php';
?>
<body>

  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-group">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // <?= htmlspecialchars($group['group_name']) ?>
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>
  
      <section>
        <?php if (empty($row)): ?>
          <div>
            <p>You're not a member of this group yet</p>
            <form method="post" action="request-join.php">
              <input type="hidden" name="group_id" value="<?= $group_id ?>">
              <button class="button" type="submit">Request to join</button>
            </form>
          </div>
        <?php else: ?>
          <article class="group">
            <div class="user-card halftone">
              <div class="avatar"></div>
              <div class="user-info">
                <span class="name"><?= htmlspecialchars($row['name']) ?></span>
                <span class="group-subject"><?= htmlspecialchars($row['subject']) ?></span>
                <p class="description"><?= htmlspecialchars($row['description']) ?></p>
            </div>
          </article>
        <?php endif; ?>
      </section>
    </div>
  </div>
  
</body>
</html>