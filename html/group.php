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
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-inbox">
                          <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                          <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                        </svg>
                      </a>
                      <a class="admin-button create-button" href="group-members.php?group_id=<?= $group_id ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users">
                          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                          <path d="M16 3.128a4 4 0 0 1 0 7.744"/>
                          <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                          <circle cx="9" cy="7" r="4"/>
                        </svg>
                      </a>
                      <form action="generate-token.php" method="post">
                        <input type="hidden" name="group_id" value="<?= $group_id ?>">
                        <button type="submit" class="admin-button create-button">
                          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="33" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" x2="19" y1="8" y2="14"/>
                            <line x1="22" x2="16" y1="11" y2="11"/>
                          </svg>
                        </button>
                      </form>
                    </div>
                  <?php endif ?>
                </div>
              </div>
            </article>
          </section>

          <?php if (isset($_SESSION['message'])): ?>
            <p class="message"><?= $_SESSION['message'] ?></p>
            <?php unset($_SESSION['message']); ?>
          <?php endif; ?>
  
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