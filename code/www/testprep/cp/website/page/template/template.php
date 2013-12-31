<?php
/**
 * Lets members choose a page template
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: template.php 1284 2004-10-30 13:33:35Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../../../site/inc/Page.class.php';
require_once '../../../../site/inc/Image.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);

$output = $Page->showHeader();

$member_id     = $Member->getId();   // Get the member's id
$page_number   = (int) $_GET['page'];      // Page number being edited
$template_id   = (int) $_GET['id'];

if (FALSE != isset($_GET['use'])) {
    // Update the member's page with the chosen template
    $update = array('template' => $template_id);
    $where = 'member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
    DB::update('cp_member_pages', $update, $where);

    // Log that the member chose a new template
    Event::logEvent(35, $member_id, $page_type, $template_id);

    header ('Location: '.$settings['cp_uri'].'/?event=35&page='.$page_number.'#page'.$page_number);
    exit;
}

$select = array('id', 'category', 'title', 'image', 'folder');
$where = 'WHERE id="'.$template_id.'"';
$result = DB::select('cp_templates', $select, $where);
$template = DB::fetchAssoc($result);

$output .= '<h2>'.$template['title'].'</h2>';

// show the template's image if it is not blank
$template_preview_image = dirname(__FILE__).'/../../../../site/templates/'.$template['folder'].'/'.$template['image'];
if ('' != $template['image'] && FALSE != is_file($template_preview_image)) {
    $folder  = $settings['site_uri'].'/site/templates/'.$template['folder'];
    // $output .= Image::showResized($folder.'/'.$template['image']);
    $output .= Image::showResized($template['image'], 'site/templates/'.$template['folder'], $settings['site_uri']);
} else {
    $output .= '<p>No template preview available</p>';
}

$output .= '<div class="button">';
$output .= '<a href="'.$_SERVER['REQUEST_URI'].'&use=1">Use This Template</a>';
$output .= '</div>';

$output .= $Page->showFooter();
echo $output;
?>
