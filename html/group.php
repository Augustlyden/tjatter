<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  if (!isset($_GET['id'])) {
    redirectAndKill('Invalid request', '/dashboard.php');
  }

  $group_id = (int)$_GET['id'];
  $err = "";

  try {
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
  } catch (Exception $e) {
    error_log('group-requests.php:' . $e->getMessage());
    $err = 'Something went wrong, try again.';
  }

?>

<?php 
  $page_name = 'Tjatter -' . htmlspecialchars($group['group_name']);
  include 'index.php';
?>
<body>

  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-dashboard">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // <?= htmlspecialchars($group['group_name']) ?>
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <div>
        <?php if (!empty($err)): ?>
          <span class="error"><?= $err ?></span>
        <?php elseif (empty($row)): ?>
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
                <div class="user-info-wrapper">
                  <div class="user-info">
                    <span class="name"><?= htmlspecialchars($row['group_name']) ?></span>
                    <span class="group-members"><?= $row['role']?></span>
                    <span class="group-subject"><?= htmlspecialchars($row['subject']) ?></span>
                    <p class="description"><?= htmlspecialchars($row['description']) ?></p>
                    <div class="create">
                      <a class="enter-button create-button" href="create-discussion.php?group_id=<?= $group_id ?>">CREATE DISCUSSION</a>
                    </div>
                  </div>
                  <?php if ($row['role'] === 'admin'): ?>
                    <div class="admin-view">
                      <a class="admin-button create-button" href="group-requests.php?group_id=<?= $group_id ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2rem" height="2rem" viewBox="0 0 36 36">
                          <path fill="currentColor" d="M32 6H4a2 2 0 0 0-2 2v20a2 2 0 0 0 2 2h28a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2m-1.54 22H5.66l7-7.24l-1.44-1.39L4 26.84V9.52l12.43 12.37a2 2 0 0 0 2.82 0L32 9.21v17.5l-7.36-7.36l-1.41 1.41ZM5.31 8h25.07L17.84 20.47Z" class="clr-i-outline clr-i-outline-path-1"/>
                          <path fill="none" d="M0 0h36v36H0z"/>
                        </svg>
                      </a>
                      <a class="admin-button create-button" href="group-members.php?group_id=<?= $group_id ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2rem" height="2rem" viewBox="0 0 24 24">
                          <path fill="none" stroke="currentColor" stroke-linecap="square" stroke-width="2" d="M16 20v-1a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v1M12.5 7a4 4 0 1 1-8 0a4 4 0 0 1 8 0Zm3 4a4 4 0 0 0 0-8M23 20v-1a4 4 0 0 0-4-4"/>
                        </svg>
                      </a>
                    </div>
                  <?php endif ?>
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
</html>