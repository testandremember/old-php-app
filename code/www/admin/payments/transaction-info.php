<?php
/**
 * Lets the admin view site stats
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: transaction-info.php 1533 2004-12-01 20:47:37Z elijah $
 * @package NiftyCMS
 * @subpackage admin_stats
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$transaction_id = (int) $_GET['id'];

// Get transaction data
$select = array('id', 'full_ipn');
$where = 'WHERE id="'.$transaction_id.'"';
$result = DB::select('paypal_transactions', $select, $where);
$transaction = DB::fetchAssoc($result);
$full_ipn = '<pre>'.$transaction['full_ipn'].'</pre>'."\n";
$transaction_info = array(
    array('label' => 'Id', 'value' => $transaction['id']),
    array('label' => 'Full IPN', 'value' => $full_ipn));
$output .= Form::showList($transaction_info);

$output .=  $Page->showFooter();
echo $output;
?>