<?php
/**
 * Logs 404 errors to the database and displays an error message to the user
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: 403-error.php 1381 2004-11-29 15:36:01Z elijah $
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><title>403 Forbidden</title>
</head>
<body>
<h1>Forbidden</h1>
<p>You don't have permission to access <?php echo $_SERVER['REQUEST_URI']; ?> on this server.</p>
</body>
</html>
<?php
require_once 'site/inc/Page.class.php';
DB::Connect($settings);
ob_start();
print_r($_SERVER);
$server = ob_get_contents();
ob_end_clean();

Event::logEvent(49, 0, 'guest', $server);
?>