<?php
/**
 * Lets members choose a page template
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../../../site/inc/Page.class.php';
require_once '../../../../site/inc/Form.class.php';
require_once '../../../../site/inc/Image.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$member_id     = $Member->getId();   // Get the member's id
$page_number   = (int) $_GET['page'];      // Page number being edited
$member_folder = $Member->getAttribute('folder_name'); // Get the member's folder

$query    = 'SELECT id, title FROM cp_template_categories ORDER BY id ASC';
$result   = DB::query($query);
$num_rows = DB::numRows($result);

$category_children = get_category_children();

$output .= '<table cellpadding="10">'."\n";
$row_ended = FALSE;
for ($column = 1; $template = DB::fetchAssoc($result); $column++) {
    if (1 == $column) {
        $output .= '<tr>';
    }
    $output .= '<td>';
    // $uri = $settings['cp_uri'].'/page/template/category.php?page='.$page_number.'&amp;id='.$template['id'];
    $uri = 'category.php?page='.$page_number.'&amp;category='.$template['id'];
    // If the category is childless then set its children number to 0
    if (FALSE == isset($category_children[$template['id']])) {
        $category_children[$template['id']] = 0;
    }
    $output .= '<a href="'.$uri.'">'.$template['title'].' ('.$category_children[$template['id']].')</a>';

    if (2 == $column) {
        $output .= '</td></tr>'."\n";
        $column = 0;
        $row_ended = TRUE;
    } else {
        $output .= '</td>'."\n";
        $row_ended = FALSE;
    }
}
if (FALSE == $row_ended) {
    $output .= '</tr>';
}
$output .= '</table>';

$output .= $Page->showFooter();
echo $output;

/**
* Get the number of children in each category
*
* @return array - number of children in each category
*/
function get_category_children() {
    $query = 'SELECT id,category FROM cp_templates ORDER BY id ASC';
    $result = DB::query($query);
    $num_rows = DB::numRows($result);
    while ($row = DB::fetchAssoc($result)) {
        // If the category has 0 children so far
        if (FALSE == isset($cat_children[$row['category']])) {
            $cat_children[$row['category']] = 1;
        } else {
            $cat_children[$row['category']]++;
        }
    }
    // Return array containing the number of children in each category
    return $cat_children;
}
?>
