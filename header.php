<?php
// Helper per gestire i percorsi in XAMPP (risolve problemi di path / vs /dashboard)
if (!function_exists('asset')) {
    function asset(string $path): string {
        // Calcola la cartella base dello script
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        // Rimuove eventuali sottocartelle note (dashboard, controller) per trovare la root dell'app
        $baseDir = preg_replace('#/(dashboard|controller|includes)$#', '', $baseDir);
        
        // Se la directory è solo '/', la rendiamo vuota per concatenazione pulita
        $baseDir = ($baseDir === '/' ? '' : $baseDir);

        return $baseDir . '/' . ltrim($path, '/');
    }
}

$title = $title ?? 'WAF-FLE Dashboard';
// Recupera tema da cookie. Uso l'attributo data-theme per il CSS moderno.
$themeClass = $_COOKIE['theme'] ?? 'dark';

// Recupero il nome utente per l'intestazione (dal branch precedente)
$userName = $_SESSION['userName'] ?? 'Guest';
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>

  <!-- Google Fonts per un look tecnico (Inter per testo, JetBrains Mono per log) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <!-- Stile Moderno -->
  <link rel="stylesheet" href="<?= asset('extra/theme.css') ?>?v=3">
  <!-- Logica UI (Dark/Light Mode, Sparklines) -->
  <script src="<?= asset('extra/ui.js') ?>"></script>
</head>
<body data-theme="<?= htmlspecialchars($themeClass) ?>">

<header class="app-header">
  <div class="container">
    <a href="<?= asset('dashboard/index.php') ?>" class="brand">
      <span>🧇</span> WAF-FLE
    </a>

    <!-- Navigazione completa (Dashboard, Filtri, Management, Setup) -->
    <nav class="nav">
      <a href="<?= asset('dashboard/index.php') ?>">Dashboard</a>
      <a href="<?= asset('filter.php') ?>">Filtro Eventi</a>
      <a href="<?= asset('management.php') ?>">Management</a>
      <a href="<?= asset('setup.php') ?>">Setup</a>
    </nav>

    <div style="display:flex; gap:12px; align-items:center;">
        <!-- Informazioni Utente e Logout -->
        <?php if(isset($_SESSION['userName'])): ?>
            <span style="font-size:12px; color:var(--muted)">User: <b><?= htmlspecialchars($userName) ?></b></span>
            <a href="<?= asset('logout.php') ?>" class="btn btn-ghost" style="padding:4px 10px; font-size:12px; border-radius:6px; text-decoration:none;">Logout</a>
        <?php endif; ?>
        
        <!-- Toggle Tema -->
        <button id="themeToggle" aria-label="Cambia tema" class="btn-icon">
        🌗
        </button>
    </div>
  </div>
</header>

<main class="container">