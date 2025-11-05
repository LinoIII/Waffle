<?php
// header.php — SOLO PRESENTAZIONE. Non toccare session/permessi esistenti qui.
// Calcolo root URL per caricare asset anche da /dashboard/*
$__BASE = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$__ROOT = preg_replace('#/dashboard$#', '', $__BASE);
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WAF-FLE</title>

  <!-- CSS/JS legacy già presenti (non rimuovere se esistono) -->
  <?php /* include di librerie storiche, grafici, ecc. */ ?>

  <!-- Tema moderno -->
  <link rel="stylesheet" href="<?php echo $__ROOT; ?>/extra/theme.css?v=1">

  <!-- UI helper -->
  <script defer src="<?php echo $__ROOT; ?>/extra/ui.js?v=1"></script>
</head>
<body class="app">
  <header class="topbar">
    <div class="brand"><a href="<?php echo $__ROOT; ?>/dashboard/index.php" style="color:inherit;text-decoration:none">WAF-FLE</a></div>
    <nav class="nav">
      <a class="nav__link" href="<?php echo $__ROOT; ?>/dashboard/index.php">Dashboard</a>
      <a class="nav__link" href="<?php echo $__ROOT; ?>/filter.php">Filtri</a>
      <a class="nav__link" href="<?php echo $__ROOT; ?>/management.php">Management</a>
      <a class="nav__link" href="<?php echo $__ROOT; ?>/setup.php">Setup</a>
    </nav>
    <div class="row">
      <span class="muted"><?php echo htmlspecialchars($_SESSION['userName'] ?? ''); ?></span>
      <button class="btn btn--ghost" data-theme-toggle aria-label="toggle theme">🌗</button>
      <a class="btn btn--err" href="<?php echo $__ROOT; ?>/logout.php">Logout</a>
    </div>
  </header>

  <main class="container">
