<?php
/**
 * Lets the admin edit and add template categories
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1102 2004-10-15 04:07:22Z elijah $
 * @package NiftyCMS
 * @subpackage admin_page_templates
*/
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$output .= '<h2>Current Templates:</h2>';
$output .= '<ul>';
$select = array('id', 'name', 'description');
$result = DB::select('page_templates', $select, '', 0);
while ($template = DB::fetchAssoc($result))
{
    $output .= '<li><a href="edit-template.php?id='.$template['id'].'">';
    $output .= '('.$template['id'].') '.$template['name'].'</a><br />';
    $output .= $template['description'].'</li>';
}
$output .= '</ul>';
$output .= $Page->showFooter();
echo $output;
?>