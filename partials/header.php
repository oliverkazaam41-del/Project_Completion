<?php
require_once __DIR__ . '/../includes/helpers.php';
$flash = flash();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Project Completion Tracker</title>
    <link rel="stylesheet" href="/assets/styles.css" />
  </head>
  <body>
    <header class="site-header">
      <h1>Project Completion Tracker</h1>
      <nav>
        <a href="/index.php">Projects</a>
        <a href="/clients.php">Clients</a>
        <a href="/project_new.php">New Project</a>
      </nav>
    </header>
    <main class="container">
      <?php if ($flash): ?>
        <div class="flash-list"><?php echo e($flash); ?></div>
      <?php endif; ?>
