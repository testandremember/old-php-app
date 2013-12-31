<?php
/**
 * Shows a filtered list of the top 100 referrers for admin, cp, dir, and
 * special pages.
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: referrers.php 1480 2004-11-30 21:17:06Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
*/
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$filterUri = $settings['site_uri'];
// $filterUri = 'df';
$select = array('name');
$result = DB::select('page_types', $select, '', 0);
$first_one = TRUE;
$query = '';
while ($page_type = DB::fetchAssoc($result)) {
    if (FALSE == isset($settings[$page_type['name'].'_uri'])) {
        $filter = $settings['site_uri'];
    } else {
        $filter = $settings[$page_type['name'].'_uri'];
    }
    $output .= show_referrers($page_type['name'], $filter);
        /*
    $output .= show_referrers('dir', );
    $output .= show_referrers('special', $settings['site_uri']);
    $output .= show_referrers('admin', $settings['admin_uri']);
    $output .= show_referrers('cp', $settings['cp_uri']);
    */

    if (FALSE == $first_one) {
        $query .= 'UNION ';
    } else {
        $first_one = FALSE;
    }
    $query .= '(SELECT COUNT(referrer) AS CNT, referrer ';
    $query .= 'FROM '.$page_type['name'].'_hit_log ';
    $query .= 'WHERE referrer NOT LIKE("%'.$filterUri.'%") ';
    $query .= 'GROUP BY referrer) ';
}
$query .= 'ORDER BY CNT DESC LIMIT 20';
$result = DB::query($query);
$output .= '<h2>Combined External Referrers</h2>';
$output .= '<p>A maximum of 200 external referrers will be shown.</p>';

$output .= '<table class="simple"><thead><tr>';
$output .= '<th>Number of Referrals</th>';
$output .= '<th>From</th>';
$output .= '</tr></thead><tbody>';
$referrers = array();
// Create an array which adds the counts of duplicate referrers together
while ($row = DB::fetchAssoc($result)) {
    if (FALSE != array_key_exists($row['referrer'], $referrers)) {
        $referrers[$row['referrer']] = $referrers[$row['referrer']] + $row['CNT'];
    } else {
        $referrers[$row['referrer']] = $row['CNT'];
    }
    /*
    $output .= '<tr>';
    $output .= '<td>'.$row["CNT"].'</td>';
    $output .= '<td>'.$row["referrer"].'</td>';
    $output .= '</tr>';
    */
}
foreach ($referrers AS $key => $value) {
    $output .= '<tr>';
    $output .= '<td>'.$value.'</td>';
    $uri = wordwrap($key, 80, '<br />', 1);
    $output .= '<td><a href="'.$key.'">'.$uri.'</a></td>';
    // $output .= '<td>'.$key.'</td>';
    $output .= '</tr>';
}


$output .= '</tbody></table>';


/*

$output .= show_referrers('dir', $settings['site_uri']);
$output .= show_referrers('special', $settings['site_uri']);
$output .= show_referrers('admin', $settings['admin_uri']);
$output .= show_referrers('cp', $settings['cp_uri']);
*/
$output .= $Page->showFooter();
echo $output;

/**
 * Shows a list of the top 100 referrers for a given page type.
 *
 * @param string $type The type of page referrers to show.
 */
function show_referrers($type, $filterUri)
{
    $output = '<h2>Top referrers to "'.$type.'" pages</h2>';
    $output .= '<p>Referrers containing: "'.$filterUri.'" have been filtered out.</p>';
    $query = 'SELECT COUNT(referrer) AS CNT, referrer FROM '.$type.'_hit_log
              WHERE referrer NOT LIKE("%'.$filterUri.'%")
              GROUP BY referrer ORDER BY CNT DESC LIMIT 100';
    $result = DB::query($query);

    $output .= '<table class="simple"><thead><tr>';
    $output .= '<th>Number of Referrals</th>';
    $output .= '<th>From</th>';
    $output .= '</tr></thead><tbody>';

    while ($row = DB::fetchAssoc($result)) {
        $output .= '<tr>';
        $output .= '<td>'.$row["CNT"].'</td>';
        $uri = wordwrap($row['referrer'], 80, '<br />', 1);
        $output .= '<td><a href="'.$row['referrer'].'">'.$uri.'</a></td>';
        // $output .= '<td>'.$row["referrer"].'</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';
    return $output;
}
?>