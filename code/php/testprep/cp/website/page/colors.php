<?php
/**
 * Allows members to choose the colors for their page.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: colors.php 1276 2004-10-30 02:15:06Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../../site/inc/Page.class.php';
require_once '../../../site/inc/Form.class.php';
$page_type = 'cp';
$Page   = new Page($page_type, $settings, $Member);
$Member = new Member($page_type, $settings, $Member);

$output = $Page->showHeader();

$member_id = $Member->getId();
$page_number = (int)$_GET['page'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the cancel button was pressed then send the member to the CP index
    Form::redirectOnCancel($settings['cp_uri'].'/#page'.$page_number);

    // Sanitize posted form data
    $posted = Form::SanitizeData($_POST);

    // Update database with the updated page color data
    $query = 'UPDATE cp_member_pages SET
                    text_color="'.$posted['text_color'].'",
                    link_color="'.$posted['link_color'].'",
                    visited_link_color="'.$posted['visited_link_color'].'",
                    background_color="'.$posted['background_color'].'"
              WHERE member_id="'.$member_id.'"
              AND page_number="'.$page_number.'"';
    DB::query($query);

    // Log that the member updated their page colors
    Event::logEvent(34, $member_id, $page_type, '');

    // Send the member to the Control Panel index
    header('Location: '.$settings['cp_uri'].'/?event=34&page='.$page_number.'#page'.$page_number);
    exit;
}
echo $output;

$query  = 'SELECT text_color, background_color, link_color, visited_link_color
           FROM cp_member_pages
           WHERE member_id="'.$member_id.'" AND page_number="'.$page_number.'"';
$result = DB::query($query);
$member_page = DB::fetchAssoc($result);

$Form = new Form($_POST, $member_page, $page_type);
?>
<script type="text/javascript" src="ColorPicker2.js"></script>
<script type="text/javascript">
var cp = new ColorPicker(); // DIV style
var cp2 = new ColorPicker('window'); // Popup window
cp.writeDiv() // Write the div color picker
</script>
<?php

// show a form that lets the member edit the font and colors  of their web page
echo $Form->startForm($_SERVER['REQUEST_URI']);

echo $Form->startFieldset('Page Colors');
echo $Form->showInfo('Choose a colors for your web page. <br />Leave field blank to use default color.');
?>
<tr><td><label for="text_color">Text color:</label></td>
<td><input style="border: 5px solid <?php echo $member_page['text_color']; ?>" type="text" id="text_color" name="text_color" onchange="if (this.value==''){this.style.border='5px solid black';} else {this.style.border = '5px solid ' + this.value;}" size="8" value="<?php echo $member_page['text_color']; ?>" />
<script type="text/javascript">
document.write('<input value="Pick Color" id="pick_text_color" name="pick_text_color" onclick="cp.select(document.getElementById(\'text_color\'),\'pick_text_color\');return false;" type="button" /> ');
document.write('<input value="Pick Color (new window)" id="pick_text_color2" name="pick_text_color2" onclick="cp2.select(document.getElementById(\'text_color\'),\'pick_text_color2\');return false;" type="button" />');
</script></td></tr>

<tr><td><label for="text_color">Link color:</label></td>
<td><input style="border: 5px solid <?php echo $member_page['link_color']; ?>" type="text" id="link_color" name="link_color" onchange="if (this.value==''){this.style.border='5px solid black';} else {this.style.border = '5px solid ' + this.value;}" size="8" value="<?php echo $member_page['link_color']; ?>" />
<script type="text/javascript" />
document.write('<input value="Pick Color" id="pick_link_color" name="pick_link_color" onclick="cp.select(document.getElementById(\'link_color\'),\'pick_link_color\');return false;" type="button" /> ');
document.write('<input value="Pick Color (new window)" id="pick_link_color2" name="pick_link_color2" onclick="cp2.select(document.getElementById(\'link_color\'),\'pick_link_color2\');return false;" type="button" />');
</script>
</td></tr>

<tr><td><label for="text_color">Visited Link color:</label></td>
<td><input style="border: 5px solid <?php echo $member_page['visited_link_color']; ?>" type="text" id="visited_link_color" name="visited_link_color" onchange="if (this.value==''){this.style.border='5px solid black';} else {this.style.border = '5px solid ' + this.value;}" size="8" value="<?php echo $member_page['visited_link_color']; ?>" />
<script type="text/javascript">
document.write('<input value="Pick Color" id="visited_pick_link_color" name="visited_pick_link_color" onclick="cp.select(document.getElementById(\'visited_link_color\'),\'visited_pick_link_color\');return false;" type="button" /> ');
document.write('<input value="Pick Color (new window)" id="visited_pick_link_color2" name="visited_pick_link_color2" onclick="cp2.select(document.getElementById(\'visited_link_color\'),\'visited_pick_link_color2\');return false;" type="button" />');
</script>
</td></tr>

<tr><td><label for="background_color">Background color:</label></td>
<td><input style="border: 5px solid <?php echo $member_page['background_color']; ?>" type="text" id="background_color" name="background_color" onchange="if (this.value==''){this.style.border='5px solid black';} else {this.style.border = '5px solid ' + this.value;}" size="8" value="<?php echo $member_page['background_color']; ?>" />
<script type="text/javascript">
document.write('<input value="Pick Color" id="pick_background_color" name="pick_background_color" onclick="cp.select(document.getElementById(\'background_color\'),\'pick_background_color\');return false;" type="button" /> ');
document.write('<input value="Pick Color (new window)" id="pick_background_color2" name="pick_background_color2" onclick="cp2.select(document.getElementById(\'background_color\'),\'pick_background_color2\');return false;" type="button" />');
</script></td></tr>

<?php
echo $Form->showSubmitButton('Save Changes', TRUE);
echo $Form->endFieldSet();
echo $Form->endForm();
echo $Page->showFooter();
?>
