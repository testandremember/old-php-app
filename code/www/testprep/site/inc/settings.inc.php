<?php
/**
 * An array of all the global settings for the entire site
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: settings.inc.php 2262 2006-11-19 02:07:27Z elijahlofgren $
 * @package NiftyCMS
 */

$settings = array (
    // Used for subdomain creation
    'domain'          => 'example.com',
    'use_subdomains'  => FALSE,
    // Only needed if use_subdomains is set to TRUE.
    'cpanel_username' => '',
    'cpanel_password' => '',
    'site_uri'        => 'http://www.elijahlofgren.com:82/testprep',
    'inc_uri'         => 'http://www.elijahlofgren.com:82/testprep/site/i',
    'admin_uri'       => 'http://www.elijahlofgren.com:82/admin',
    'cp_uri'          => 'http://www.elijahlofgren.com:82/testprep/cp',
    'templates_dir'   => 'http://www.elijahlofgren.com:82/testprep/site/templates',
    'db_server'       => 'localhost',
    'db_username'     => 'testprep',
    'db_password'     => 'mysql_password_here',
    'db_name'         => 'testprep',
    'use_ftp'         => FALSE,
    // Used to chmod member's folders to 777 (only needed if use_ftp == TRUE)
    'ftp_server'      => '',
    'ftp_username'    => '',
    'ftp_password'    => '',
    'ftp_path'        => '/',
    'email_from'      => 'elijah@lofgren.us',
    'error_email'     => 'elijah@lofgren.us',
    'backup_email'    => 'automated@elijahlofgren.com',
    'debug'           => FALSE,
    // Change to www.paypal.com to process real payments.
    // Change to www.sandbox.paypal.com to test out payments
    'paypal_domain'   => 'www.sandbox.paypal.com',
    'paypal_email'    => ''
);

/*
$settings = array (
    'domain'          => 'testprep.localhost',
    'use_subdomains'  => FALSE,
    // Only needed if use_subdomains is set to TRUE.
    'cpanel_username' => '',
    'cpanel_password' => '',
    'site_uri'        => 'http://testprep.localhost',
    'inc_uri'         => 'http://testprep.localhost/site/i',
    'admin_uri'       => 'http://testprep.localhost/admin',
    'cp_uri'          => 'http://testprep.localhost/cp',
    'templates_dir'   => 'http://testprep.localhost/site/templates',
    'db_server'       => 'localhost',
    'db_username'     => 'root',
    'db_password'     => 'mysql_password_here',
    'db_name'         => 'testprepniftycms',
    'use_ftp'         => FALSE,
    // Used to chmod member's folders to 777 (only needed if use_ftp == TRUE)
    'ftp_server'      => '',
    'ftp_username'    => '',
    'ftp_password'    => '',
    'ftp_path'        => '/public_html/',
    'email_from'      => 'elijah@lofgren.us',
    'error_email'     => 'elijah@lofgren.us',
    'backup_email'    => 'elijah@lofgren.us',
    'debug'           => TRUE,
    // Change to www.paypal.com to process real payments.
    // Change to www.sandbox.paypal.com to test out payments
    'paypal_domain'   => 'www.sandbox.paypal.com',
    'paypal_email'    => 'elijah@lofgren.us'
);
*/
// Image settings
$image_settings = array (
    'max_upload_at_once' => 3,
    'thumbnail_height'   => 123,
    'thumbnail_width'    => 179,
    // Max height before image is resized
    'max_height'         => 600,
    // Max width before image is resized
    'max_width'          => 600,
    // Max file size in bytes
    'max_file_size'      => 1000000,
    // JPG image quality
    'image_quality'       => 50
);
// Settings for plan 1
$plan_settings[1] = array(
    'num_pages'    => 1,
    'num_pictures' => 4,
    'year_prices'  => array(
        1 => '60.00',
        2 => '110.00',
        3 => '160.00',
        4 => '240.00'
    )
);
// Settings for plan 2
$plan_settings[2] = array(
    'num_pages'    => 2,
    'num_pictures' => 19,
    'year_prices'  => array(
        1 => '100.00',
        2 => '166.00',
        3 => '241.90',
        4 => '400.00'
    )
);
// Settings for plan 3
$plan_settings[3] = array(
    'num_pages'    => 3,
    'num_pictures' => 34,
    'year_prices'  => array (
        1 => '140.00',
        2 => '210.00',
        3 => '327.20',
        4 => '560.00'
    )
);
?>
