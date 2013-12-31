<?php
/**
 * Shows member's pages
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: show-page.php 1228 2004-10-27 00:32:48Z elijah $
 * @package NiftyCMS
 */
require_once dirname(__FILE__).'/../inc/Page.class.php';
require_once dirname(__FILE__).'/../inc/PageMaker.class.php';

$page_type = 'special';
// $Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
// $header = $Page->showHeader();
DB::connect($settings);
$Page->sendNoCacheHeaders();

if (FALSE == isset($member_folder_name)) {
    die('Error: $member_folder_name is not set.');
}

// Show the member's page
// $PageMaker = new PageMaker($member_id, $member_folder, $settings['site_uri']);
$PageMaker = new PageMaker($settings, $member_folder_name);
// $page_id = $PageMaker->getPageId();
$page_content = $PageMaker->showPage();
// echo 'Your page id is: '.$page_id;
echo $page_content;
?>
