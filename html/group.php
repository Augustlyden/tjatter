<?php 
  require 'settings.php';
  require 'includes/functions.php';

  $group_id = (int)$_GET['id'];

  if (isset($_SESSION['user_id'])) {
    $groupCheck = $db->prepare(
      'SELECT * FROM groups_ WHERE id = :id;'
    );
    $groupCheck->execute([':id' => $group_id]);
    $group = $groupCheck->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
      redirectAndKill('This "group" does not exist.', '/groups.php');
    }

    $stmt = $db->prepare(
      'SELECT 
        g.id AS group_id,
        g.group_name,
        g.subject,
        g.members,
        g.description,
        gm.role
      FROM groups_ g
      JOIN group_members gm ON gm.group_id = g.id
      JOIN users u ON gm.user_id = u.id 
      WHERE g.id = :group_id AND u.id = :user_id AND gm.status = \'approved\';'
    );
    $stmt->execute([':group_id' => $group_id, ':user_id' => $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
      $discussionStmt = $db->prepare(
        'SELECT * FROM discussions
        WHERE group_id = :group_id
        ORDER BY discussions.created_at;'
      );
      $discussionStmt->execute([':group_id' => $group_id]);
      $discussions = $discussionStmt->fetchAll(PDO::FETCH_ASSOC);
    }
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

      <div>
        <?php if (empty($row)): ?>
          <section>
            <div>
              <p>You're not a member of this group yet</p>
              <form method="post" action="request-join.php">
                <input type="hidden" name="group_id" value="<?= $group_id ?>">
                <button class="button" type="submit">Request to join</button>
              </form>
            </div>
          </section>
        <?php else: ?>
          <section>
            <article class="group">
              <div class="user-card halftone">
                <div class="avatar"></div>
                <div class="user-info">
                  <span class="name"><?= htmlspecialchars($row['group_name']) ?></span>
                  <span class="group-subject"><?= htmlspecialchars($row['subject']) ?></span>
                  <p class="description"><?= htmlspecialchars($row['description']) ?></p>
                  <div class="create">
                    <a class="enter-button create-button" href="create-discussion.php?group_id=<?= $group_id ?>">CREATE DISCUSSION</a>
                  </div>
                </div>
              </div>
            </article>
          </section>
  
          <section class="group-grid">
            <?php if (!$discussions): ?>
              <div class="empty-rows">
                <p>There are no discussions here yet.</p>
              </div>
            <?php else: ?>
              <?php foreach ($discussions as $discussion): ?>
                <article class="panel group-card">
                  <span class="pixel-bubble"></span>
                  <div class="group-card-body">
                    <h2><?= htmlspecialchars($discussion['discussion_name']) ?></h2>
                    <div class="enter">
                      <a class="enter-button" href="discussion.php?id=<?= (int)$discussion['id'] ?>">Enter</a>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </div>
  
    </div>
  </div>
  
</body>