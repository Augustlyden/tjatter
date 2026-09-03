<?php
  require 'settings.php';
  require 'includes/functions.php';

  $discussionId = (int)$_GET['id'];

  if (isset($_SESSION['user_id'])) {
    $discussionCheck = $db->prepare(
      'SELECT * FROM discussions WHERE id = :id;'
    );
    $discussionCheck->execute([':id' => $discussionId]);
    $discussion = $discussionCheck->fetch(PDO::FETCH_ASSOC);

    if (!$discussion) {
      redirectAndKill('This "discussion" does not exist.', '/dashboard.php');
    }

    $stmt = $db->prepare(
      'SELECT
        d.id,
        d.discussion_name,
        d.created_at,
        u.username
      FROM discussions d
      JOIN users u ON d.user_id = u.id
      JOIN group_members gm ON gm.group_id = d.group_id
      WHERE d.id = :d_id AND gm.user_id = :u_id AND gm.status = \'approved\';'
    );
    $stmt->execute([':d_id' => $discussionId, ':u_id' => $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
      $postStmt = $db->prepare(
        'SELECT 
          p.post,
          p.created_at,
          u.username
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.discussion_id = :d_id
        ORDER BY p.created_at;'
      );
      $postStmt->execute([':d_id' => $discussionId]);
      $posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);
    }
  } else {
    header('Location: /login.php');
    die();
  }
?>

<?php 
  $page_name = 'Tjatter -' . htmlspecialchars($discussion['discussion_name']);
  include 'index.php';
?>

<body>

  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
    <div class="window-group">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // <?= htmlspecialchars($discussion['discussion_name']) ?>
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <div>
        <?php if (empty($row)): ?>
          <section>
            <div>
              <p>Join the group to take part in this discussion.</p>
              <form method="post" action="request-join.php">
                <input type="hidden" name="group_id" value="<?= $discussion['group_id'] ?>">
                <button class="button" type="submit">Request to join</button>
              </form>
            </div>
          </section>
        <?php else: ?>
          <section>
            <article class="group">
              <div class="user-card halftone">
                <div class="user-info">
                  <span class="name"><?= htmlspecialchars($row['discussion_name']) ?></span>
                  <span class="group-subject">created by: @<?= htmlspecialchars($row['username']) ?></span>
                  <p class="group-subject">created: <?= $row['created_at'] ?></p>
                </div>
              </div>
            </article>
          </section>
  
          <section class="posts-section">
            <?php if (!$posts): ?>
              <div class="empty-rows">
                <p>There are no posts here yet.</p>
              </div>
            <?php else: ?>
              <?php foreach ($posts as $post): ?>
                <article class="panel post-card">
                  <div class="group-card-body">
                    <h2>@<?= htmlspecialchars($post['username'])?></h2>
                    <p class="post"><?= htmlspecialchars($post['post'])?></p>
                    <p class="group-subject">posted: <?= $post['created_at'] ?></p>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
          <form class="post-form" action="create-post.php?discussion_id=<?= $discussionId ?>" method="post">
            <input type="text" name="post" placeholder="Type your answer...">
            <button type="submit">POST</button>
            <?php if (isset($_SESSION['err'])): ?>
              <p class="error"><?= $_SESSION['err'] ?></p>
              <?php unset($_SESSION['err']); ?>
            <?php endif; ?>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>