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
  $discussionNameErr = $dbErr = $postErr = "";
  $discussionName = $post = "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
      if (empty($_POST['discussionName'])) {
        $discussionNameErr = 'A title is required';
      } else {
        $discussionName = trim($_POST['discussionName']);
      }
  
      if (empty($_POST['post'])) {
        $postErr = 'A first post is required';
      } else {
        $post = trim($_POST['post']);
      }
  
      $groupCheck = $db->prepare(
        'SELECT * FROM groups_ WHERE id = :id;'
      );
      $groupCheck->execute([':id' => $groupId]);
      $group = $groupCheck->fetch(PDO::FETCH_ASSOC);
  
      if (!$group) {
        redirectAndKill('This "group" does not exist.', '/groups.php');
      }
  
      $memberCheck = $db->prepare(
        'SELECT * FROM group_members 
        WHERE user_id = :user_id AND group_id = :group_id AND status = \'approved\';'
      );
      $memberCheck->execute([
        ':user_id' => $_SESSION['user_id'],
        ':group_id' => $groupId,
      ]);
      $member = $memberCheck->fetch(PDO::FETCH_ASSOC);
  
      if (!$member) {
        redirectAndKill("You're not a member of this group. Join to create a discussion", '/groups.php');
      }
  
      if (empty($discussionNameErr) && empty($postErr)) {
        $db->beginTransaction();
        try {
          $stmt = $db->prepare(
            'INSERT INTO discussions (group_id, user_id, discussion_name)
            VALUES (:group_id, :user_id, :discussion_name)'
          );
          $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $_SESSION['user_id'],
            ':discussion_name' => $discussionName
          ]);
          $discussionId = $db->lastInsertId();
  
          $postStmt = $db->prepare(
            'INSERT INTO posts (discussion_id, user_id, post)
            VALUES (:discussion_id, :user_id, :post)'
          );
          $postStmt->execute([
            ':discussion_id' => $discussionId,
            ':user_id' => $_SESSION['user_id'],
            ':post' => $post
          ]);
  
          $db->commit();
          header('Location: /discussion.php?id=' . $discussionId);
          die();
        } catch (Exception $e) {
          $db->rollBack();
          $dbErr = 'Something went wrong, try again';
          error_log('create-discussion.php:' . $e->getMessage());
        }
      }
    } catch (\Throwable $th) {
      error_log('create-discussion.php:' . $e->getMessage());
      $dbErr = 'Something went wrong, try again.';
    }
  }
?>

<?php 
  $page_name = 'Tjatter - Create Discussion';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
  
    <div class="window">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // CREATE DISCUSSION
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <div class="window-body">
        <h1 class="title">Create Discussion</h1>
        <form class="form" method="post" action="create-discussion.php?group_id=<?= $groupId ?>">
          <div>
            <label for="discussionName"><span class="required">*</span> Title</label>
            <input class="input" type="text" id="discussionName" name="discussionName" value="<?= htmlspecialchars($discussionName) ?>">
            <span class="<?= $discussionNameErr ? 'error' : '' ?>"><?= $discussionNameErr ?></span>
          </div>

          <div>
            <label for="post"><span class="required">*</span> First post</label>
            <textarea class="textarea" id="post" name="post"><?= htmlspecialchars($post) ?></textarea>
            <span class="<?= $postErr ? 'error' : '' ?>"><?= $postErr ?></span>
          </div>
  
          <button class="button" type="submit">Create</button>
          <span class="<?= $dbErr ? 'error' : '' ?>"><?= $dbErr ?></span>
  
        </form>
      </div>

    </div>
  </div>
  
</body>
</html>