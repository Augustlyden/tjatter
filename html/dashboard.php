<?php 
  require 'data.php';

  $myGroups = array_filter($allGroups, function($group) use ($myGroupIds) {
    return in_array($group['id'], $myGroupIds);
  });
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tjatter - Dashboard</title>
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="page-layout">

    <?php include 'sidebar.php'; ?>

    <div class="window-group">
      <div class="window-titlebar">
        <span class="pixel-bubble"></span>
        TJATTER // DASHBOARD
        <div class="window-controls">
          <span></span><span></span><span></span>
        </div>
      </div>

      <section class="group-grid">
        <?php foreach ($myGroups as $group): ?>
          <article class="panel group-card">
            <span class="pixel-bubble"></span>
            <div class="group-card-body">
              <h2><?= htmlspecialchars($group['name']) ?></h2>
              <p class="group-subject"><?= htmlspecialchars($group['subject']) ?></p>
              <p class="group-members"><?= (int)$group['members'] ?> members</p>
              <div class="enter">
                <a class="enter-button" href="group.php?id=<?= $group['id'] ?>">Enter Group</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    </div>

  </div>

</body>
</html>