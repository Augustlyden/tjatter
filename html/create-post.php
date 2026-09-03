<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  $discussionId = (int)$_GET['discussion_id'];

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['post'])) {
      redirectAndKill('Type something to post.', '/discussion.php?id=' . $discussionId);
    } else {
      $post = trim($_POST['post']);
    }

    $discussionCheck = $db->prepare(
      'SELECT * FROM discussions
      WHERE id = :id;'
    );
    $discussionCheck->execute([':id' => $discussionId]);
    $discussion = $discussionCheck->fetch(PDO::FETCH_ASSOC);

    if (!$discussion) {
      redirectAndKill('This discussion does not exist.', '/dashboard.php');
    }

    $memberCheck = $db->prepare(
      'SELECT * FROM discussions d
      JOIN group_members gm ON gm.group_id = d.group_id
      WHERE d.id = :d_id AND gm.user_id = :u_id AND gm.status = \'approved\';'
    );
    $memberCheck->execute([':d_id' => $discussionId, ':u_id' => $_SESSION['user_id']]);
    $member = $memberCheck->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
      redirectAndKill("You're not a member of this discussion", '/dashboard.php');
    }

    $stmt = $db->prepare(
      'INSERT INTO posts (discussion_id, user_id, post)
      VALUES (:discussion_id, :user_id, :post);'
    );
    $stmt->execute([
      ':discussion_id' => $discussionId,
      ':user_id' => $_SESSION['user_id'],
      ':post' => $post
    ]);
    header('Location: /discussion.php?id=' . $discussionId);
    die();
  }
?>