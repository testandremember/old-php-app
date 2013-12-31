<?php
/**
 * Lets the admin edit and add template categories
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1671 2005-01-03 16:10:52Z elijah $
 * @package NiftyCMS
 * @subpackage admin_question_categories
*/
require_once '../../../testprep/testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

echo $output;
?>
<b>Current Question Categories:</b>
<ul>
<?php
$query='SELECT id,title FROM prep_question_categories';
$result=DB::query($query);
while ($title=DB::fetchAssoc($result))
{
    echo '<li><a href="edit-category.php?id='.$title['id'].'">('.$title['id'].') '.$title['title'].'</a></li>';
}
?>
</ul>

<?php
echo $Page->showFooter();
?>