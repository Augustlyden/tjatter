<?php 
  $groups = [
    11 => ['id' => 11, 'name' => 'Beyond Horizon', 'subject' => 'Horizon zd and fb', 'members' => 36],
    9 => ['id' => 9, 'name' => 'Build lego', 'subject' => 'Lego builds', 'members' => 32],
    4=> ['id' => 4, 'name' => 'Critters', 'subject' => 'Critical Role', 'members' => 999],
    1 => ['id' => 1, 'name' => 'GameGang', 'subject' => 'All about games', 'members' => 9],
    10 => ['id' => 10, 'name' => 'Sand!Which?', 'subject' => 'Delicious sandwiches', 'members' => 27],
    12 => ['id' => 12, 'name' => 'Swimming', 'subject' => 'Those who loves to swim', 'members' => 28]
  ];
  
  $groupId = (int) $_GET['id'];
  
  $group = $groups[$groupId] ?? null;

  if (!$group) {
    die('Group not found');
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tjatter - <?= htmlspecialchars($group['name']) ?></title>
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <div class="window-group">
    <div class="window-titlebar">
      <span class="pixel-bubble"></span>
      TJATTER // <?= htmlspecialchars($group['name']) ?>
      <div class="window-controls">
        <span></span><span></span><span></span>
      </div>
    </div>

    <section>
      <article>
        <?= htmlspecialchars($group['subject']) ?>
      </article>
    </section>

  </div>
  
</body>
</html>