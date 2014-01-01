<?php
/**
 * The Admin Panel home.
 *
 * At the Admin Panel you can edit various options
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1355 2004-11-01 02:24:50Z elijah $
 * @package NiftyCMS
 * @subpackage admin
 */
require_once '/var/www/elijahlofgren/testprep/testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

// show the message if there is one
if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$output .= '<div class="title">Admin Menu</div>'."\n";

// Get the Admin Panel menu
$Menu = new Menu('admin', 0, FALSE);
$output .= $Menu->showListMenu($page_type, $settings['site_uri'], 0);

$output .= $Page->showFooter();
echo $output;
?>
