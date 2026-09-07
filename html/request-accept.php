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
  
    $db->beginTransaction();
    $stmt = $db->prepare(
      'UPDATE group_members
      SET status = :status
      WHERE user_id = :user_id AND group_id = :group_id;'
    );
    $stmt->execute([
      ':status' => 'approved',
      ':user_id' => $userId, 
      ':group_id' => $groupId
    ]);
    
    $stmt = $db->prepare(
      'UPDATE groups_
      SET members = members + 1
      WHERE id = :group_id;'
    );
    $stmt->execute([':group_id' => $groupId]);

    $db->commit();
    header('Location: /group-requests.php?group_id=' . $groupId);
    die();
  } catch (Exception $e) {
    if ($db->inTransaction()) {
      $db->rollBack();
    }
    error_log('request-accept.php:' . $e->getMessage());
    redirectAndKill('Something went wrong, try again', '/group-requests.php?group_id=' . $groupId);
  }
?>