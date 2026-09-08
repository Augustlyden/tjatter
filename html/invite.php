<?php
  require 'settings.php';
  require 'includes/functions.php';

  if (!isset($_GET['token'])) {
    redirectAndKill('Invalid request', '/dashboard.php');
  }

  $token = $_GET['token'];

  if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/invite.php?token=' . $token;
    header('Location: /login.php');
    die();
  }

  try {
    $tokenCheck = $db->prepare(
      'SELECT 
        g.id,
        g.group_name
      FROM invites i
      JOIN groups_ g on i.group_id = g.id
      WHERE token = :token AND used_at IS NULL AND expires_at > NOW();'
    );
    $tokenCheck->execute([':token' => $token]);
    $row = $tokenCheck->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      redirectAndKill("This invite is invalid or has expired.", '/dashboard.php');
    }

    $groupId = (int)$row['id'];

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
      redirectAndKill("You're already a member of this group.", '/dashboard.php');
    }

    $db->beginTransaction();
    $stmt = $db->prepare(
      'INSERT INTO group_members (user_id, group_id, role, status)
      VALUES (:user_id, :group_id, :role, :status);'
    );
    $stmt->execute([
      ':user_id' => $_SESSION['user_id'],
      ':group_id' => $groupId,
      ':role' => 'member',
      ':status' => 'approved'
    ]);

    $stmt = $db->prepare(
      'UPDATE groups_
      SET members = members + 1
      WHERE id = :group_id;'
    );
    $stmt->execute([':group_id' => $groupId]);

    $stmt = $db->prepare(
      'UPDATE invites
      SET used_at = NOW()
      WHERE token = :token;'
    );
    $stmt->execute([':token' => $token]);

    $db->commit();
    redirectAndKill("You're now a member of this group!", '/group.php?id=' . $groupId);
    header('Location: /group.php?id=' . $groupId);
    die();
  } catch (Exception $e) {
    if ($db->inTransaction()) {
      $db->rollBack();
    }
    error_log('invite.php:' . $e->getMessage());
    redirectAndKill('Something went wrong, try again', '/dashboard.php');
  }
?>