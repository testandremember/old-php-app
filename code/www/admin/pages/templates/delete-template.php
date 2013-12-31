<?php
/**
 * Lets the admin delete a page template
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-template.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 * @subpackage admin_page_templates
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$template_id = (int) $_GET['id'];
// Delete a template from the database
DB::delete('page_templates', 'id="'.$template_id.'"');
DB::close();
header('Location: '.$settings['admin_uri'].'/templates/');
?>
