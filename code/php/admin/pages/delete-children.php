<?php
/**
 * Lets the admin delete all the children of a page.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-children.php 1360 2004-11-01 03:18:48Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$member_id = $Member->getId();
// $Page = new Page($page_type, $settings, $Member);

// DB::Connect($settings);

// delete all the children pages of a page
$query = 'DELETE FROM '.$_GET['type'].'_pages WHERE parent_id='.$_GET['id'];
DB::query($query);

Event::logEvent(82, $member_id, $page_type, '');

DB::close();

header('Location: '.$settings['admin_uri'].'/pages/child-delete.php?event=82');
?>
