<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  function redirectAndKill($message) {
    $_SESSION['err'] = $message;
    header('Location: /groups.php');
    die();
  }

  if  ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['group_id'])) {
    $groupCheck = $db->prepare(
      'SELECT * FROM groups_ WHERE id = :id;'
    );
    $groupCheck->execute([':id' => $_POST['group_id']]);
    $group = $groupCheck->fetch(PDO::FETCH_ASSOC);

    $memberCheck = $db->prepare(
      'SELECT * FROM group_members 
      WHERE user_id = :user_id AND group_id = :group_id;'
    );
    $memberCheck->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $_POST['group_id'],
    ]);
    $member = $memberCheck->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
      redirectAndKill('This "group" does not exist.');
    }

    if ($member) {
      redirectAndKill("You're already a member of this group.");
    }

    $stmt = $db->prepare(
      'INSERT INTO group_members (user_id, group_id, role, status)
      VALUES (:user_id, :group_id, :role, :status);'
    );
    $stmt->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $_POST['group_id'],
      ':role' => 'member',
      ':status' => 'pending'
    ]);

    header('Location: /groups.php');
    die();
  }
?>