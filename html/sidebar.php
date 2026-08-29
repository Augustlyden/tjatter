<?php 
  $currentPage = basename($_SERVER['PHP_SELF'], ".php");

  $navLinks = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php'],
    'groups' => ['label' => 'Groups', 'href' => 'groups.php'],
    'create-group' => ['label' => 'Create Group', 'href' => 'create-group.php'],
    'logout' => ['label' => 'Logout', 'href' => 'logout.php']
  ];
?>

<aside class="sidebar">
  <div class="window-titlebar">
    MENU
    <div class="window-controls">
      <span></span><span></span>
    </div>
  </div>
  <nav class="sidebar-nav">
      <?php foreach ($navLinks as $key => $link): ?>
        <a href="<?= $link['href'] ?>" class="<?= $currentPage === $key ? 'active' : '' ?>">
          <?= $link['label'] ?>
        </a>
      <?php endforeach; ?>
  </nav>
</aside>