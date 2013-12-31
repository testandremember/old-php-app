<?php
/**
 * Allows the admin to quickly delete pages
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: quick-delete.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$Tree = new Tree($settings);
$output .= $Page->showAdminHeader();
$output .= '<p style="color:red; font-size:1.5em">WARNING! Pages are deleted without confirmation. Be careful!</p>';
$output .= '<h2 id="dir">Directory Pages</h2>';
$output .= $Tree->showPageAdminMenu('dir', $settings['admin_uri'].'/pages/delete-page.php?type=dir&amp;id=');
$output .= '<h2 id="admin">Admin Pages</h2>';
$output .= $Tree->showPageAdminMenu('admin', $settings['admin_uri'].'/pages/delete-page.php?type=admin&amp;id=');
$output .= '<h2 id="cp">CP Pages</h2>';
$output .= $Tree->showPageAdminMenu('cp', $settings['admin_uri'].'/pages/delete-page.php?type=cp&amp;id=');
$output .= '<h2 id="special">Special Pages</h2>';
$output .= $Tree->showPageAdminMenu('special', $settings['admin_uri'].'/pages/delete-page.php?type=special&amp;id=');

$output .= $Page->showFooter();
echo $output;
?>
