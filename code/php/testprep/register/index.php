<?php
/**
 * Lets users register and pay using paypal
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1465 2004-11-30 16:02:03Z elijah $
 * @package NiftyCMS
 * @subpackage register
 */
require_once '../site/inc/Page.class.php';
require_once '../site/inc/Form.class.php';
require_once '../site/inc/Account.class.php';
$page_type = 'special';
$Page   = new Page($page_type, $settings, $Member);
$Member = new Member('cp', $settings);
$output2 = $Page->showHeader();
$output = '';
$Account = new Account($settings);
$show_form = TRUE;

// Trim spaces, and convert quotes to html entities
$posted = Form::sanitizeData($_POST);

$signup_id = $Account->getSignUpId();

if (FALSE != isset($_GET['plan'])) {
    $plan_number = (int) $_GET['plan'];
} else {
    $plan_number = 0;
}

if (FALSE == isset($_COOKIE['signup_id'])) {
    $new_registration = TRUE;
    // Create a form object
    $Form = new Form($posted,$posted);
} else {
    $query = 'SELECT folder_name, duration_years, num_pages, num_pictures,
                     email, login_name, password, last_paid, first_name,
                     last_name, address1, address2, city, state, zip,
                     night_phone, day_phone FROM cp_members
              WHERE signup_id="'.$signup_id.'" LIMIT 1';
    $result = DB::query($query);
    if (1 == DB::numRows($result)) {
        $new_registration = FALSE;
        $preset_values = DB::fetchAssoc($result);
        // Fill in the confirm email field so that the users do not have to
        // retype it
        $preset_values['email_confirm'] = $preset_values['email'];
        // Create a form object
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $Form = new Form($posted, $posted, $page_type, $settings);
        } else {
            $Form = new Form($posted, $preset_values, $page_type, $settings);
        }
    } else {
        $new_registration = TRUE;
        // Create a form object
        $Form = new Form($posted, $posted, $page_type, $settings);
    }
}

if ('POST' == $_SERVER['REQUEST_METHOD']) {

    // Validate the member's folder name
    $valid_dir_name = $Form->isValidDirName('folder_name', $_POST['folder_name']);
    // Only check if the directory is taken if the folder name is
    // formatted correctly.
    if (FALSE != $valid_dir_name) {
        $dir_taken = $Account->isDirTaken($_POST['folder_name'], $signup_id);
        if (FALSE != $dir_taken) {
            // That folder is already taken. Please choose another name.
            $Form->SetError('folder_name', 25);
        }
    }
    /*
    // Validate the member's email address
    $valid_email = $Form->IsEmail('email');
    if (FALSE != $valid_email) {
    $email_taken = $SignUp->isAccountItemTaken('email', $_POST['email'], $signup_id);
        if (FALSE != $email_taken) {
            // An account with that email address already exists. Please use a different email addresss.
            $Form->SetError('email', 26);
        }
    }
    */
    // Validate the member's login name
    $valid_login_name = $Form->isAlphanumeric('login_name');
    if (FALSE != $valid_login_name) {
    $login_name_taken = $Account->isAccountItemTaken('login_name', $_POST['login_name'], $signup_id);
        if (FALSE != $login_name_taken) {
            // That login name is already in use. Please choose a different login name.
            $Form->SetError('login_name', 66);
        }
    }


    $Form->areEqual('email', 'email_confirm', 'Email Addresses');
    $Form->hasPickedOption('duration_years');
    $Form->isFilledIn('first_name');
    $Form->isFilledIn('last_name');
    $Form->isFilledIn('address1');
    $Form->isFilledIn('city');
    $Form->hasPickedOption('state');
    $Form->isZipCode('zip');
    $Form->isPhoneNumber('night_phone', FALSE);
    $Form->isPhoneNumber('day_phone', TRUE);
    $Form->isValidPassword('password', $_POST['password']);
    $Form->areEqual('password', 'password_confirm');
    $Form->isChecked('tos_agree');
    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {
        // Save the member's registration information into the database
        $Account->saveRegistrationInfo($posted, $new_registration, $signup_id, $plan_number, $plan_settings);

       /* TODO: Log form input success */
       // $states_array = $Form->getStates();
       // $output .= $SignUp->showRegistrationInfo($signup_id, $states_array, $settings['site_uri'], $plan_settings);
       $output .= $Account->confirmInfo($signup_id, $plan_settings, $posted, $plan_number);
       $current_step = 2;

       // Don't show the registration form
       $show_form = FALSE;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, 0, $page_type, '');
    }
}

// If we are supposed to show the registration form
if (0 != $plan_number && FALSE != $show_form) {
    // show a form that lets people register
    $output .= '<h2>Enter Registration Information</h2>'."\n";
    $output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
    $output .= $Form->startFieldset('Account Plan');
    /*
    $pages_array = array('1' => '1 Page', '2' => '2 Pages', '3' => '3 Pages');
    $output .= $Form->showDropDown('Number of Pages', 'num_pages', $pages_array);
    $pictures_array = array('10' => '10 Pictures', '20' => '20 Pictures', '30' => '30 Pictures');
    $output .= $Form->showDropDown('Number of Pictures', 'num_pictures', $pictures_array);
    */

    $output .= $Form->showText('Plan Number', $plan_number);
if ($plan_settings[$plan_number]['num_pages'] == 1) {
    $pages = ' page';
} else {
    $pages = ' pages';
}
    $output .= $Form->showText('Number of Pages', $plan_settings[$plan_number]['num_pages'].$pages);
    $output .= $Form->showText('Max Number of Pictures', $plan_settings[$plan_number]['num_pictures'].' pictures');
    $duration_array = array(
                            '1' => '1 Year $'
                            .$plan_settings[$plan_number]['year_prices'][1],
                            '2' => '2 Years $'
                            .$plan_settings[$plan_number]['year_prices'][2],
                            '3' => '3 Years $'
                            .$plan_settings[$plan_number]['year_prices'][3],
                            '4' => '4 Years $'
                            .$plan_settings[$plan_number]['year_prices'][4]
                            );
    $output .= $Form->showDropDown('Duration', 'duration_years', $duration_array);
    $output .= $Form->endFieldset();
    $output .= $Form->startFieldset('Web Site URL');
    $output .= $Form->showInfo('<b>Subdomain Name</b> - Choose a subdomain name for your web site. It must only contain letters, and dashes.<br />For example you if you chose "companyname" your web site URL would be: http://companyname.example.com/.');
    $show_after = '<br />(e.g., companyname)';
    $output .= $Form->showTextInput('Subdomain Name', 'folder_name', 100, '', $show_after);
    $output .= $Form->endFieldset();
    $output .= $Form->startFieldset('Address Information');
    $output .= $Form->showInfo('<b>Address Information</b> - Fill in your address information as you have it listed on your credit card or bank account');
    $output .= $Form->showTextInput('First Name', 'first_name', 32);
    $output .= $Form->showTextInput('Last Name', 'last_name', 64);
    $output .= $Form->showTextInput('Address 1', 'address1', 100);
    $output .= $Form->showTextInput('Address 2 (optional)', 'address2', 100);
    $output .= $Form->showTextInput('City', 'city', 100);
    $states = $Form->getStates();
    $output .= $Form->showDropDown('State', 'state', $states);
    $show_after = '(5 or 9 digits)';
    $output .= $Form->showTextInput('Zip Code ', 'zip', 10, 12, $show_after);
    $label = 'Night Phone';
    $show_after = '<br /> (use 000-000-0000 format)';
    $output .= $Form->showTextInput($label, 'night_phone', 12, 12, $show_after);
    $output .= $Form->showTextInput('Day Phone (optional)', 'day_phone', 12, 12, $show_after);
    $output .= $Form->endFieldset();
    $output .= $Form->startFieldset('Account Information');
    $output .= $Form->showInfo('<b>Your Account Information</b> - Your login name will be used to login to your Control Panel. Your password must be at least <b>6 characters long</b> and is case sensitive. Try to make your password as unique as possible.');
    $output .= $Form->showTextInput('Login Name', 'login_name', 255, 0);
    $output .= $Form->showTextInput('Email Address', 'email', 127, 0);
    $output .= $Form->showTextInput('Confirm Email Address', 'email_confirm',  127, 0);
    $uri = $settings['site_uri'].'/support/password-tips.php';
    $show_after = '<br />'.$Form->showNewWindowLink('Password Tips', $uri);
    $output .= $Form->showPasswordInput('Password', 'password', $show_after);
    $output .= $Form->showPasswordInput('Confirm Password', 'password_confirm');
    $output .= $Form->endFieldset();

    $output .= $Form->startFieldset('Terms of Service Agreement');
    $uri = $settings['site_uri'].'/tos/';
    $show_after = 'I have read and agree to the '.$Form->showNewWindowLink('Terms of Service', $uri).'.';
    $output .= $Form->showCheckbox('TOS', 'tos_agree', 1, $show_after);
    $output .= $Form->showSubmitButton('Submit Registration', FALSE);
    $output .= $Form->endFieldset();

    $output .= $Form->endForm();
    $current_step = 1;
} else {
    if (0 == $plan_number) {
        $output .= 'Error: Invalid or no plan number specified. Please go to the <a href="'.$settings['site_uri'].'/hosting/">Web Hosting</a> page.';
        $current_step = 1;
    }
}

$steps_array = array(1 => 'Enter Registration Info', 2 => 'Confirm Info',
                     3 => 'Pay using PayPal'
                    );
$output2 .= $Form->showSteps($steps_array, $current_step);
$output2 .= $output;

// Output the page to the client
$output2 .= $Page->showFooter();
echo $output2;
?>
