<?PHP
/*
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
?>
<?PHP
require_once "../functions.php";

// DEBUG LOCALE: mostra errori a schermo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


global $DEBUG;
if ($DEBUG) {
    $starttime_main = microtime(true);
}
ini_set("session.cookie_httponly", 1);
session_start();
 
if (isset($_POST['submit']) && $_POST['submit'] == "submit") {
    if ($_POST['user'] == "" || $_POST['pass'] == "") {
        $emptyField = true;
    } else {
        $username  = @sanitize_paranoid_string($_POST['user']);
        $password  = $_POST['pass'];
        $ref       = @sanitize_paranoid_string($_POST['ref']);
        $userlogon = checkUser($username, $password);

        if ($userlogon[0]['result']) {
            $_SESSION['login']    = true;
            $_SESSION['userName'] = ucfirst(strtolower($userlogon[0]['username']));
            $_SESSION['userID']   = $userlogon[0]['user_id'];
            $_SESSION['email']    = $userlogon[0]['email'];
            $_SESSION['admin']    = $userlogon[0]['admin'];
            $_SESSION['LAST_ACTIVITY'] = time(); // define first "last activity" timestamp
            $_SESSION['CREATED'] = time(); // initialize the session create timestamp

            if (isset($userlogon[0]['changePass']) && $userlogon[0]['changePass']) {
               $_SESSION['forceChangePass'] = true;
            } 
            session_regenerate_id(true);    // change session ID for the current session an invalidate old session ID
            header("HTTP/1.1 302 Found");
            header("Location: index.php");
            header("Connection: close");
            header("Content-Type: text/html; charset=UTF-8");
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
        	   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		   <title>WAF-FLE</title>
		   <link rel="stylesheet" type="text/css" href="css/main.css" />  
		  </head>
		<body>
		<div id="header">
		<div id="logo"> <a style="padding: 0;" href="./index.php"><img src="images/logo.png" width="126" height="60" border="0" alt="ModSecurity Dashboard"></a></div>
		<div id="clear"> </div>
		</div>
		<div id="page-wrap">
 		<h2>You are now logged in!</h2><br>Please check if your browser support http redirect (status 302). If not, <a href="index.php"> click here to access WAF-FLE</a>
		<br>Have a nice WAF-FLing<br>
		<br><br><br><br><br><br><br><br><br>
		</div>
	<?PHP
		$hideFilter = true;
		require_once "../footer.php";
		exit();
        } else {
            $authFailed = true;
        }
    }
}
?>


<?php
// Frontend login moderno
$title = 'Login';
$hideFilter = true; // nel caso footer/header la usino
require_once "../header.php";
?>

<div class="login-wrapper">
  <section class="card login-card">
    <h2 class="login-title">Accedi a WAF-FLE</h2>
    <p class="login-subtitle">Inserisci le credenziali per continuare.</p>

    <?php if (isset($emptyField) && $emptyField): ?>
      <p class="alert alert-error">
        Compila sia username che password.
      </p>
    <?php elseif (isset($authFailed) && $authFailed): ?>
      <p class="alert alert-error">
        Username o password non validi.
      </p>
    <?php elseif (isset($userExpired) && $userExpired): ?>
      <p class="alert alert-error">
        Utente scaduto.
      </p>
    <?php endif; ?>

    <form action="login.php" method="POST" class="form-grid" autocomplete="off">
      <div class="form-row">
        <label for="user">Username</label>
        <input
          type="text"
          id="user"
          name="user"
          autocomplete="off"
          class="auto-focus"
          autofocus
        >
      </div>

      <div class="form-row">
        <label for="pass">Password</label>
        <input
          type="password"
          id="pass"
          name="pass"
          autocomplete="off"
        >
      </div>

      <input
        type="hidden"
        name="ref"
        value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? ''); ?>"
      >

      <div class="form-actions" style="justify-content:flex-end;">
        <button type="submit" name="submit" value="submit" class="btn">
          Accedi
        </button>
      </div>
    </form>
  </section>
</div>

<?php
require_once "../footer.php";
?>
