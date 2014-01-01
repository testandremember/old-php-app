<?php
/**
 * The TruthLand sitemap
 * @version $Id: index.php 1658 2004-12-25 07:52:46Z elijah $
 * @package NiftyCMS
 * @subpackage www
 */
 require_once '../site/inc/Page.class.php';
$page_type = 'www';
$Member = new Member('cp', $settings);
$Page   = new Page($page_type, $settings, $Member);
$output  = $Page->showHeader();
$output .= $Page->showFooter();
echo $output;
?>
