<?php
/**
 * Lets the admin delete a member type
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-type.php 1181 2004-10-25 00:58:46Z elijah $
 * @package NiftyCMS
 * @subpackage admin_member_types
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$type_id = (int) $_GET['id'];
// Delete a member type from the database
DB::delete('member_types', 'id="'.$type_id.'"');
DB::close();
header('Location: '.$settings['admin_uri'].'/members/types/');
?>
