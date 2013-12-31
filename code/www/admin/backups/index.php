<?php
/**
 * Backup section
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 * @subpackage admin_docs
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= 'Welcome to the Backups section.';

$output .= $Page->showFooter();
echo $output;
?>
