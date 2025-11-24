<?php
// Helper per avere path corretti sia da root che da /dashboard/
function asset(string $path): string {
    $scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    // Se siamo in /qualcosa/dashboard o /qualcosa/controller, togli l'ultima parte
    $scriptDir = preg_replace('#/(dashboard|controller)$#', '', $scriptDir);

    if ($scriptDir === '/' || $scriptDir === '') {
        $scriptDir = '';
    }

    return $scriptDir.'/'.ltrim($path, '/');
}

$title = $title ?? 'WAF-FLE';
// Tema di default: dark
$bodyClass = $_COOKIE['theme'] ?? 'theme-dark';
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>

  <!-- CSS + JS frontend -->
  <link rel="stylesheet" href="<?= asset('extra/theme.css') ?>">
  <script defer src="<?= asset('extra/ui.js') ?>"></script>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<header class="app-header">
  <div class="container">
    <h1>WAF-FLE</h1>

    <nav class="nav">
      <a href="<?= asset('dashboard/index.php') ?>">Dashboard</a>
      <a href="<?= asset('filter.php') ?>">Filtro</a>
    </nav>

    <button id="themeToggle" aria-label="Cambia tema" class="btn-icon">
      🌓
    </button>
  </div>
</header>

<main class="container">
