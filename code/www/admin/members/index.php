<?php
/**
 * The Admin Panel members section
 *
 * @version $Id: index.php 1412 2004-11-29 20:08:29Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$output .= 'Welcome to the members section.<br />';
$output .= 'Here you can add, edit and delete the members of this website.<br />';
$output .= 'Choose a member type from the menu.';

$output .= $Page->showFooter();
echo $output;
?>
