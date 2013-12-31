<?php
/**
 * Backup section
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: backup-database.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 * @subpackage admin_backups
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= 'Creating Database backup.';
$output .= DB::backup($settings);

$output .= $Page->showFooter();
echo $output;
?>