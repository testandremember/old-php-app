<?php
/**
 * Shows a list of PayPal Instant Payment Notifications (IPN)
 *
 * @version $Id: index.php 1535 2004-12-01 21:36:57Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_payments
 */
require_once '../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$output .= 'Welcome to the payments section.<br />';
$output .= 'Here you can view the payments recieved through PayPal.<br />';

$output .= '<h2>PayPal Instant Payment Notifications</h2>'."\n";
$output .= '<table class="simple">'."\n";
$output .= '<thead>'."\n";
$output .= '<tr>'."\n";
$output .= '<th>Id</th>'."\n";
$output .= '<th>Payment Gross</th>'."\n";
$output .= '<th>Date</th>'."\n";
$output .= '<th>Payer Name</th>'."\n";
$output .= '<th>Payer Email</th>'."\n";
$output .= '<th>Status</th>'."\n";
$output .= '<th>Test IPN</th>'."\n";
$output .= '</tr>'."\n";
$output .= '</thead>'."\n";
$output .= '<tbody>'."\n";

$select = array('id', 'first_name', 'last_name', 'payment_gross',
                'payment_date', 'payment_status', 'payer_email', 'test_ipn');
$options = 'ORDER BY id DESC';
$result = DB::select('paypal_transactions', $select, $options, 0);
$show_stripe = TRUE;
while ($transaction = DB::fetchAssoc($result)) {
    if (FALSE == $show_stripe) {
        $output .= '<tr>'."\n";
        $show_stripe = TRUE;
    } else {
        $output .= '<tr class="stripe">'."\n";
        $show_stripe = FALSE;
    }
    $output .= '<td><a href="transaction-info.php?id='.$transaction['id'].
        '">'.$transaction['id'].'</a></td>'."\n";
    $output .= '<td>$'.$transaction['payment_gross'].'</td>'."\n";
    $output .= '<td>'.$transaction['payment_date'].'</td>'."\n";
    $output .= '<td>'.$transaction['first_name'].' '.$transaction['last_name'].'</td>'."\n";
    $output .= '<td>'.$transaction['payer_email'].'</td>'."\n";
    $output .= '<td>'.$transaction['payment_status'].'</td>'."\n";
    $output .= '<td>'.$transaction['test_ipn'].'</td>'."\n";
    $output .= '</tr>'."\n";
        }
$output .= '</tbody>'."\n";
$output .= '</table>'."\n";

$output .= $Page->showFooter();
echo $output;
?>
