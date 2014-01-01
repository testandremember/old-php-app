<?php
/**
 * Lets the admin edit a member type
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-type.php 1331 2004-10-30 23:42:20Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members_types
 *
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$type_id = (int) $_GET['id'];

$select = array('id', 'name', 'title', 'description', 'can_login', 'has_website');
$result = DB::select('member_types', $select, 'WHERE id="'.$type_id.'"');
$type = DB::fetchAssoc($result);
// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = Form::sanitizeData($_POST);
    if (FALSE == empty($posted['can_login'])) {
        $posted['can_login'] = 1;
    } else {
        $posted['can_login'] = 0;
    }
    if (FALSE == empty($posted['has_website'])) {
        $posted['has_website'] = 1;
    } else {
        $posted['has_website'] = 0;
    }

    $update = array('name' => $posted['name'], 'title' => $posted['title'],
                    'description' => $posted['description'],
                    'can_login' => $posted['can_login'],
                    'has_website' => $posted['has_website']);
    DB::update('member_types', $update, 'id = "'.$type_id.'"');

    DB::close();
    header('Location: '.$settings['admin_uri'].'/members/types/');
    exit;
}

$Form = new Form($_POST, $type);

// $target = 'delete-type.php?id='.$type_id;
// $message = 'Are you sure you want to delete this member type?';
// $output .= $Form->showConfirmButton($target, 'Delete Member Type', $message);

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Member Type');
$output .= $Form->showTextInput('Name', 'name', 255);
$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextInput('Description', 'description', 255);
$output .= $Form->showCheckbox('Can login', 'can_login', 1);
$output .= $Form->showCheckbox('Has Website', 'has_website', 1);
$more_buttons = array(1 =>
                      array('label' => 'Delete Member Type',
                            'name' => 'delete_member_type',
                            'description' => 'You can delete this member from the database.',
                            'confirm_message' => 'Are you sure you want to delete this member type?'));
$output .= $Form->showSubmitButton('Update Member Type', TRUE, $more_buttons);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>