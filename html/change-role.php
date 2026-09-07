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

  $groupId = (int)$_POST['group_id'];
  $userId = (int)$_POST['user_id'];
  $role = "";

  try {
    $adminCheck = $db->prepare(
      'SELECT * FROM users u
      JOIN group_members gm ON gm.user_id = u.id
      WHERE u.id = :user_id AND gm.group_id = :group_id;'
    );
    $adminCheck->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $groupId
    ]);
    $admin = $adminCheck->fetch(PDO::FETCH_ASSOC);
  
    if (!$admin || $admin['role'] !== 'admin') {
      redirectAndKill("You don't have access to this site", '/dashboard.php');
    }

    if ($_POST['role'] !== 'admin' && $_POST['role'] !== 'member') {
      redirectAndKill('Invalid input', '/group-members.php?group_id=' . $groupId);
    } else {
      $role = $_POST['role'];
    }

    $stmt = $db->prepare(
      'UPDATE group_members
      SET role = :role
      WHERE user_id = :user_id AND group_id = :group_id;'
    );
    $stmt->execute([
      ':role' => $role,
      ':user_id' => $userId,
      ':group_id' => $groupId
    ]);
    header('Location: /group-members.php?group_id=' . $groupId);
    die();
  } catch (Exception $e) {
    error_log('change-role.php:' . $e->getMessage());
    redirectAndKill('Something went wrong, try again', '/group-members.php?group_id=' . $groupId);
  }
?>