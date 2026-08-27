<?php 
  require 'data.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tjatter - Groups</title>
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <div class="window-group">
    <div class="window-titlebar">
      <span class="pixel-bubble"></span>
      TJATTER // ALL GROUPS
      <div class="window-controls">
        <span></span><span></span><span></span>
      </div>
    </div>

    <section class="group-grid">
      <?php foreach ($allGroups as $group): ?>
        <article class="panel group-card">
          <span class="pixel-bubble"></span>
          <div class="group-card-body">
            <h2><?= htmlspecialchars($group['name']) ?></h2>
            <p class="group-subject"><?= htmlspecialchars($group['subject']) ?></p>
            <p class="group-members"><?= (int)$group['members'] ?> members</p>
            <button type="button">Request to join</button>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  </div>
  
</body>
</html>