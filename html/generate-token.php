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
  $bytes = random_bytes(5);
  $token = bin2hex($bytes);

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

    $stmt = $db->prepare(
      'INSERT INTO invites (token, group_id, created_by, expires_at) 
      VALUES (:token, :group_id, :created_by, NOW() + INTERVAL 24 HOUR)'
    );
    $stmt->execute([
      ':token' => $token,
      ':group_id' => $groupId,
      ':created_by' => $_SESSION['user_id']
    ]);
    header('Location: /show-invite.php?token=' . $token);
    die();
  } catch (Exception $e) {
    error_log('generate-token.php:' . $e->getMessage());
    redirectAndKill('Something went wrong, try again', '/group.php?id=' . $groupId);
  }
?>