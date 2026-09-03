<?php
  require 'settings.php';
  require 'includes/functions.php';

  $groupName = $subject = $description = "";
  $groupNameErr = $subjectErr = $descErr = $dbErr = "";

  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['groupName'])) {
      $groupNameErr = 'A title is required';
    } else {
      $groupName = trim($_POST['groupName']);
    }

    if (empty($_POST['subject'])) {
      $subjectErr = 'A subject is required';
    } else {
      $subject = trim($_POST['subject']);
    }

    if (empty($_POST['description'])) {
      $descErr = 'A description is required';
    } else {
      $description = trim($_POST['description']);
    }

    if (empty($groupNameErr) && empty($subjectErr) && empty($descErr)) {
      $db->beginTransaction();
      try {
        $stmt = $db->prepare(
          'INSERT INTO groups_ (group_name, subject, description, members)
          VALUES (:group_name, :subject, :description, :members)'
        );
        $stmt->execute([
          ':group_name' => $groupName,
          ':subject' => $subject,
          ':description' => $description,
          ':members' => 1
        ]);
        $newGroupId = $db->lastInsertId();

        $userStmt = $db->prepare(
          'INSERT INTO group_members (user_id, group_id, role, status)
          VALUES (:user_id, :group_id, :role, :status)'
        );
        $userStmt->execute([
          ':user_id' => $_SESSION['user_id'],
          ':group_id' => $newGroupId,
          ':role' => 'admin',
          ':status' => 'approved'
        ]);

        $db->commit();
        header('Location: /group.php?id=' . $newGroupId);
        die();
      } catch (Exception $e) {
        $db->rollBack();
        $dbErr = 'Something went wrong, try again';
        error_log('create-group.php:' . $e->getMessage());
      }
    }
  }
?>

<?php 
  $page_name = 'Tjatter - Create Group';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
  
    <div class="window">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // CREATE GROUP
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <div class="window-body">
        <h1 class="title">Create Group</h1>
        <form class="form" method="post" action="create-group.php">
          <div>
            <label for="groupName"><span class="required">*</span> Title</label>
            <input class="input" type="text" id="groupName" name="groupName" value="<?= htmlspecialchars($groupName) ?>">
            <span class="<?= $groupNameErr ? 'error' : '' ?>"><?= $groupNameErr ?></span>
          </div>
  
          <div>
            <label for="subject"><span class="required">*</span> Subject</label>
            <input class="input" type="text" id="subject" name="subject" value="<?= htmlspecialchars($subject) ?>">
            <span class="<?= $subjectErr ? 'error' : '' ?>"><?= $subjectErr ?></span>
          </div>
  
          <div>
            <label for="description"><span class="required">*</span> Description</label>
            <textarea class="textarea" id="description" name="description"><?= htmlspecialchars($description) ?></textarea>
            <span class="<?= $descErr ? 'error' : '' ?>"><?= $descErr ?></span>
          </div>
  
          <button class="button" type="submit">Create</button>
          <span class="<?= $dbErr ? 'error' : '' ?>"><?= $dbErr ?></span>
  
        </form>
      </div>

    </div>
  </div>
  
</body>
</html>