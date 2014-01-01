<?php
/**
 * Lets members choose a page template
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: category.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page_templates
 */
require_once '../../../../site/inc/Page.class.php';
require_once '../../../../site/inc/Form.class.php';
require_once '../../../../site/inc/Image.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);

$output = $Page->showHeader();

$member_id     = $Member->getId();   // Get the member's id
$page_number      = (int) $_GET['page'];      // Page number being edited
$member_folder = $Member->getAttribute('folder_name'); // Get the member's folder
$category_id = (int) $_GET['category'];
$query = 'SELECT id, title, image, thumbnail_image, folder, category FROM cp_templates
          WHERE category="'.$category_id.'" ORDER BY id ASC';
$result = DB::query($query);
$num_rows = DB::numRows($result);
$output .= '<table cellpadding="10">';
$row_ended = 0;
for ($i = 1; $template = DB::fetchAssoc($result); $i++) {
    if (1 == $i) {
        $output .= '<tr>';
    }
    $output .= '<td>';

    $output .= '<a href="template.php?page='.$page_number.'&amp;id='.$template['id'].'&amp;category='.$template['category'].'">';
    $output .= '<img src="'.$settings['site_uri'].'/site/templates/'.$template['folder'].'/'.$template['thumbnail_image'].'" alt=""/><br />';
    $output .= $template['title'].'</a>';

    if (2 == $i) {
        $output .= '</td></tr>'."\n";
        $i = 0;
        $row_ended = 1;
    } else {
        $row_ended = 0;
        $output .= '</td>'."\n";
    }
}
if (1 != $row_ended) {
    $output .= '</tr>';
}
$output .= '</table>';
$output .= $Page->showFooter();
echo $output;
?>
