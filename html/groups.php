<?php 
  require 'data.php';
?>

<?php 
  $page_name = 'Tjatter - All Groups';
  include 'index.php';
?>
<body>
  <div class="page-layout">
    <?php include 'sidebar.php'; ?>
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
  </div>

  
</body>
</html>