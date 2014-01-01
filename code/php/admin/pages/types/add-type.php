<?php
/**
 * Lets the admin add page types
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-type.php 1225 2004-10-27 00:15:06Z elijah $
 * @package NiftyCMS
 * @subpackage admin_pages_types
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$Form = new Form($_POST,array());

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::sanitizeData($_POST);
    if (FALSE == isset($posted['force_login']) OR 1 != $posted['force_login']) {
        $posted['force_login'] = 0;
    } else {
        $posted['force_login'] = 1;
    }
    if (FALSE == isset($posted['prevent_caching']) OR 1 != $posted['prevent_caching']) {
        $posted['prevent_caching'] = 0;
    } else {
        $posted['prevent_caching'] = 1;
    }
    $insert = array('id' => '',
                    'name' => $posted['name'],
                    'title' => $posted['title'],
                    'description' => $posted['description'],
                    'template' => $posted['template'],
                    'force_login' => $posted['force_login'],
                    'prevent_caching' => $posted['prevent_caching']);
    DB::insert('page_types', $insert);
    DB::close();
    header('Location: '.$settings['admin_uri'].'/pages/types/');
    exit;
}

$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Add new page type');
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$select = array('id', 'name');
$result = DB::select('page_templates', $select, '', 0);
$template_title = array();
while ($template = DB::fetchAssoc($result)) {
    $template_title[$template['id']] = $template['name'];
}
$output .= $Form->showDropDown('Template','template',$template_title);
$output .= $Form->showCheckbox('Force Login', 'force_login', 1);
$output .= $Form->showCheckbox('Prevent Page Caching', 'prevent_caching', 1);
$output .= $Form->showSubmitButton('Add new page type');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
