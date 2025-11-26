<?php
// FILE: index.php (NELLA ROOT)
// Questo file gestisce il LOGIN.
require_once 'config.php';
require_once 'functions.php';

session_start();

// Se l'utente è già loggato, lo mandiamo subito alla dashboard
if (isset($_SESSION['user_id']) || isset($_SESSION['login'])) {
    header("Location: dashboard/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Usa la funzione checkUser presente in functions.php
    $userCheck = checkUser($username, $password);

    if (isset($userCheck[0]['result']) && $userCheck[0]['result'] === TRUE) {
        $_SESSION['user_id'] = $userCheck[0]['user_id'];
        $_SESSION['userName'] = $userCheck[0]['username'];
        $_SESSION['login'] = TRUE;
        session_refresh();
        
        // Login ok -> Vai alla Dashboard
        header("Location: dashboard/index.php");
        exit;
    } else {
        $error = "Credenziali non valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - WAF-FLE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="extra/theme.css?v=<?= time() ?>"> 
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand">
            <span style="color:var(--primary); font-size:2rem;">🛡</span> 
            <span>WAF-FLE</span>
        </div>
        <p style="color:var(--text-muted); margin-bottom:20px;">Secure Management Console</p>
        
        <?php if ($error): ?>
            <div style="color:var(--danger); background:rgba(248,81,73,0.1); padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid var(--danger); font-size:0.9rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">Accedi</button>
        </form>
        
        <div class="login-footer">
            &copy; <?= date('Y') ?> WAF-FLE Project
        </div>
    </div>
</div>
</body>
</html>