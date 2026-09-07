<?php 
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectAndKill('Invalid request', '/dashboard.php');
  }

  if (!isset($_POST['group_id'])) {
    redirectAndKill('Invalid request', '/dashboard.php');
  }

  $groupId = (int)$_POST['group_id'];

  try {
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
      WHERE user_id = :user_id AND group_id = :group_id;'
    );
    $memberCheck->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $groupId,
    ]);
    $member = $memberCheck->fetch(PDO::FETCH_ASSOC);

    if ($member) {
      redirectAndKill("You're already a member of this group.", '/groups.php');
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
  } catch (Exception $e) {
    error_log('group-requests.php:' . $e->getMessage());
    redirectAndKill('Something went wrong, try again', '/groups.php');
  }
?>