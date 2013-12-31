<?php
/**
 * Lets the admin delete a page subtype
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-subtype.php 1103 2004-10-15 04:22:54Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_subtypes
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$type_id = (int) $_GET['id'];
// Delete a page subtype from the database
DB::delete('page_subtypes', 'id="'.$type_id.'"');
DB::close();
header('Location: '.$settings['admin_uri'].'/pages/subtypes/');
?>
