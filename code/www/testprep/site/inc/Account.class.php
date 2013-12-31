<?php
/**
 * Functions used when a someone registers for a new account
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Account.class.php 1543 2004-12-01 23:29:41Z elijah $
 * @package NiftyCMS
 */

// Account->delete() uses the Member class
require_once 'Member.class.php';

class Account {
    // An array of sitewide settings
    var $mSettings = array();

    /**
     * Initializes the class.
     *
     * @param array $settings - An array of sitewide settings
     */
    function Account($settings)
    {
        $this->mSettings = $settings;
    }

    /**
     * Sets a cookie so that we can identify anyone who signs up
     *
     * @return $token string - A 32 char hex string which is the member's
     * unique signup id.
     */
    function getSignUpId()
    {
        // Only set the cookie if it is not already set
        if (FALSE == isset($_COOKIE['signup_id'])) {
            $token = $this->setSignUpId();
        } else {
            $current_token = DB::makeSafe($_COOKIE['signup_id']);
            $query = "SELECT account_setup FROM cp_members WHERE signup_id='".$current_token."' LIMIT 1";
            $result = DB::query($query);
            $member = DB::fetchAssoc($result);
            if ($member['account_setup'] != 1) {
                $token = DB::MakeSafe($_COOKIE['signup_id']);
            } else {
                $token = $this->setSignUpId();
            }
        }
        return $token;
    }

    /**
     * Sets a unique signup_id cookie on the user's computer.
     *
     * @private
     */
    function setSignupId() {
        $num_rows = 1;
        while (0 != $num_rows) {
            // Generate a unique token to be used during signup to identify the user
            $token = md5(uniqid(rand(), TRUE));
            $query = "SELECT id FROM cp_members WHERE signup_id='".$token."' LIMIT 1";
            $result = DB::query($query);
            $num_rows = DB::numRows($result);
        }
            // Set cookie to expire in 2 days
            $cookie_expire = time()+60*60*24*2;
            // Set signup cookie on the users machine so that we can identify them
            setcookie('signup_id', $token, $cookie_expire, '/');
            return $token;
    }

    /**
     * Outputs a button that lets the user pay for his subscription
     *
     * @param $addressInfo array - Contains the member's address info which
     * we will pass to paypal via hidden form fields.
     * @return $output string - The form which contains the Pay button
     */
    function showPayButton($addressInfo, $price, $planNumber)
    {
       $output = '<form action="https://'.$this->mSettings['paypal_domain'].'/cgi-bin/webscr" method="post" />'."\n";
       $output .= '<input type="submit" name="submit" value="Continue to Payment &raquo;" />'."\n";
       $output .= '<input type="hidden" name="cmd" value="_xclick" />'."\n";
       // $output .= '<input type="hidden" name="cmd" value="_ext-enter" />'."\n";
       /// $output .= '<input type="hidden" name="redirect_cmd" value="_xclick" />'."\n";
       $output .= '<input type="hidden" name="business" value="'.$this->mSettings['paypal_email'].'" />'."\n";
       $output .= '<input type="hidden" name="item_name" value="Member Site" />'."\n";
       $output .= '<input type="hidden" name="item_number" value="'.$planNumber.'" />'."\n";
       $output .= '<input type="hidden" name="amount" value="'.$price.'" />'."\n";
       $output .= '<input type="hidden" name="no_shipping" value="1" />'."\n";
       $output .= '<input type="hidden" name="cbt" value="Continue to Login" />'."\n";
       $output .= '<input type="hidden" name="return" value="/cp/login.php?event=36" />'."\n";
       $output .= '<input type="hidden" name="cancel_return" value="/" />'."\n";
       // $output .= '<input type="hidden" name="no_note" value="1" />'."\n";
       $output .= '<input type="hidden" name="currency_code" value="USD" />'."\n";
       $output .= '<input type="hidden" name="lc" value="US" />'."\n";
       $output .= '<input type="hidden" name="email" value="'.$addressInfo['email'].'" />'."\n";
       $output .= '<input type="hidden" name="first_name" value="'.$addressInfo['first_name'].'" />'."\n";
       $output .= '<input type="hidden" name="last_name" value="'.$addressInfo['last_name'].'" />'."\n";
       $output .= '<input type="hidden" name="address1" value="'.$addressInfo['address1'].'" />'."\n";
       $output .= '<input type="hidden" name="address2" value="'.$addressInfo['address2'].'" />'."\n";
       $output .= '<input type="hidden" name="city" value="'.$addressInfo['city'].'" />'."\n";
       $output .= '<input type="hidden" name="state" value="'.$addressInfo['state'].'" />'."\n";
       $output .= '<input type="hidden" name="zip" value="'.$addressInfo['zip'].'" />'."\n";
       // If the night phone number was sent
       if (FALSE != ereg ("([0-9]{3})-([0-9]{3})-([0-9]{4})", $addressInfo['night_phone'], $night_phone)) {
           $output .= '<input type="hidden" name="night_phone_a" value="'.$night_phone[1].'" />'."\n";
           $output .= '<input type="hidden" name="night_phone_b" value="'.$night_phone[2].'" />'."\n";
           $output .= '<input type="hidden" name="night_phone_c" value="'.$night_phone[3].'" />'."\n";
       }
       // If the day phone number was sent
       if (FALSE != ereg ("([0-9]{3})-([0-9]{3})-([0-9]{4})", $addressInfo['day_phone'], $day_phone)) {
           $output .= '<input type="hidden" name="day_phone_a" value="'.$day_phone[1].'" />'."\n";
           $output .= '<input type="hidden" name="day_phone_b" value="'.$day_phone[2].'" />'."\n";
           $output .= '<input type="hidden" name="day_phone_c" value="'.$day_phone[3].'" />'."\n";
       }
       if (FALSE != isset($_COOKIE['signup_id'])) {
           $output .= '<input type="hidden" name="custom" value="'.$_COOKIE['signup_id'].'" />'."\n";
       } else {
           $signup_id = $this->setCookie();
           $output .= '<input type="hidden" name="custom" value="'.$signup_id.'" />'."\n";
       }
       $output .= '</form />'."\n";
       return $output;
    }

    function confirmInfo($signupId, $planSettings, $postArray, $planNumber)
    {
        // Get the member's registration info out of the database
        $query = 'SELECT folder_name, plan, duration_years, num_pages,
                         num_pictures, email, login_name, password, last_paid, first_name,
                         last_name, address1, address2, city, state, zip,
                         night_phone, day_phone
                  FROM cp_members WHERE signup_id="'.$signupId.'" LIMIT 1';
        $result = DB::query($query);
        if (1 == DB::numRows($result)) {
            $member_data = DB::fetchAssoc($result);
        } else {
            return Event::showAndLog(11, 0, $query);
        }

        // show the registration info
        $output = $this->showRegistrationInfo($signupId, $planSettings, $member_data);

        $output .= '<p class="infobox"> If the information above is correct please click the "Continue to Payment" button.</p>'."\n";
        // show a back button that lets the member go back and change their
        // registration data if they see that something is incorrect.
        $output .= '<div style="float:left">'."\n";

        $output .= '<form action="'.$this->mSettings['site_uri'].'/register/" method="get">'."\n";
        $output .= '<input type="submit" value="&laquo; Back">'."\n";
        $output .= '<input type="hidden" name="plan" value="'.$planNumber.'">'."\n";
        $output .= '</form>'."\n";
        $output .= '</div>'."\n";
        // show the button that lets them pay for their account
        $output .= '<div style="text-align:right" class="submit">'."\n";
        // show the pay button
        $output .= $this->showPayButton($postArray, $planSettings[$member_data['plan']]['year_prices'][$member_data['duration_years']], $member_data['plan']);
        $output .= '</div>'."\n";
        $output .= '<p class="infobox">If you need to change any of the above information please click the "Back" button.</p>'."\n";

        return $output;
    }

     /**
      * Shows the member's registration info (used on the confirmation page)
      *
      * @param $signupId string - A 32 char hex string which is the member's
      * unique signup id.
      */
    function showRegistrationInfo($signupId, $planSettings, $member)
    {
       $output  = '<h2>Verify Information</h2>';
       $output .= '<p class="infobox">Please take a moment and verify that ';
       $output .= 'your information is correct.</p>'."\n";
        // show the member's registration information
      $duration_years = $member['duration_years'].' ';
        if (1 == $member['duration_years']) {
            $duration_years .= 'year';
        } else {
            $duration_years .= 'years';
        }
        $price = '$'.$planSettings[$member['plan']]['year_prices'][$member['duration_years']];
        $website_url = 'http://'.$member['folder_name'].'.'.$this->mSettings['domain'].'/';
        $address = $member['first_name'].' ';
        $address .= $member['last_name'].'<br />'."\n";
        $address .= $member['address1'].'<br />'."\n";
        if ('' != $member['address2']) {
            $address .= $member['address2'].'<br />'."\n";
        }
        $address .= $member['city'].', ';
        $address .= $member['state'].' ';
        $address .= $member['zip']."\n";
        $confirm = array(
            array('label' => 'Plan Number', 'value' => $member['num_pages']),
            array('label' => 'Pages', 'value' => $member['num_pages']),
            array('label' => 'Pictures', 'value' => $member['num_pictures']),
            array('label' => 'Duration', 'value' => $duration_years),
            array('label' => 'Price', 'value' => $price),
            array('label' => 'Website URL', 'value' => $website_url),
            array('label' => 'Login Name', 'value' => $member['login_name']),
            array('label' => 'Email Address', 'value' => $member['email']),
            array('label' => 'Address', 'value' => $address),
            array('label' => 'Night Phone', 'value' => $member['night_phone']),
            array('label' => 'Day Phone', 'value' => $member['day_phone']),
        );
        $output .= Form::showList($confirm);
        return $output;
    }

    /**
     * Saves the member's registration information into the database
     *
     * @param $memberData array - Contains the member's registration information
     * @param $new_registration bool - Whether or not this is a registration
     * info update or a completely new registration.
     * @param $signupId string - A 32 char hex string which is the member's
     * unique signup id.
     * @returns $query_result int - Returns 1 if the query was successful
     */
    function saveRegistrationInfo($memberData, $new_registration, $signupId, $plan, $planSettings)
    {
        // Encrypt the password before storing it in the database
        $password = md5($memberData['password']);

        $num_pages = $planSettings[$plan]['num_pages'];
        $num_pictures = $planSettings[$plan]['num_pictures'];

        // If this is not a completely new registration (i.e. the are just
        // changing their information)
       if (FALSE == $new_registration) {
           // If the member is going back and changing their registration data
           $update = array('folder_name'    => $memberData['folder_name'],
                           'plan'           => $plan,
                           'duration_years' => $memberData['duration_years'],
                           'num_pages'      => $num_pages,
                           'num_pictures'   => $num_pictures,
                           'email'          => $memberData['email'],
                           'login_name'     => $memberData['login_name'],
                           'password'       => $password,
                           'account_active' => 0,
                           'last_paid'      => 0,
                           'first_name'     => $memberData['first_name'],
                           'last_name'      => $memberData['last_name'],
                           'address1'       => $memberData['address1'],
                           'address2'       => $memberData['address2'],
                           'city'           => $memberData['city'],
                           'state'          => $memberData['state'],
                           'zip'            => $memberData['zip'],
                           'night_phone'    => $memberData['night_phone'],
                           'day_phone'      => $memberData['day_phone'],
                           'account_setup'  => 0);
           $where = 'signup_id="'.$signupId.'"';
           $query_result = DB::update('cp_members', $update, $where);
       } else {
           // This is a new registration. Create a new database row
           $insert = array('id'             => '',
                           'folder_name'    => $memberData['folder_name'],
                           'plan'           => $plan,
                           'duration_years' => $memberData['duration_years'],
                           'num_pages'      => $num_pages,
                           'num_pictures'   => $num_pictures,
                           'email'          => $memberData['email'],
                           'login_name'     => $memberData['login_name'],
                           'password'       => $password,
                           'account_active' => 0,
                           'last_paid'      => 0,
                           'first_name'     => $memberData['first_name'],
                           'last_name'      => $memberData['last_name'],
                           'address1'       => $memberData['address1'],
                           'address2'       => $memberData['address2'],
                           'city'           => $memberData['city'],
                           'state'          => $memberData['state'],
                           'zip'            => $memberData['zip'],
                           'night_phone'    => $memberData['night_phone'],
                           'day_phone'      => $memberData['day_phone'],
                           'signup_id'      => $signupId,
                           'account_setup'  => 0,
                           'creation_date'  => time());
           $query_result = DB::insert('cp_members', $insert);
       }
       return $query_result;
    }

    /**
     * Checks to see if a folder is alreay in use.
     *
     * @param $folderName string - The folder name to check
     * @return bool - TRUE if folder is taken. FALSE if folder is unused.
     */
    function isDirTaken($folderName, $signupId)
    {
        // Get the name of the folder which contains this file
        $current_folder = dirname(__FILE__);
        // See if $folderName exists two directories up from this file's folder
        $is_dir = file_exists($current_folder.'/../../'.$folderName.'/');
        // If it is not a current folder
        if (FALSE == $is_dir) {
            // Check to make sure the folder is not going to be
            // used by another member
            $folder_name = DB::MakeSafe($folderName);
            $query = 'SELECT id FROM cp_members
                      WHERE folder_name="'.$folder_name.'"
                      AND signup_id != "'.$signupId.'" LIMIT 1';
            $result = DB::query($query);
            $num_rows = DB::numRows($result);
            // If the folder is not taken
            if (0 == $num_rows) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    /**
     * Checks to see if an item of a member's account already taken such as
     * their login name or email address.
     *
     * @param $itemName string - The name of the item being checked
     * @param $itemValue string - The value of the item being checked
     * @return bool - TRUE if email is taken. FALSE if email is unused.
     */
    function isAccountItemTaken($itemName, $itemValue, $signupId)
    {
        $itemValue = DB::MakeSafe($itemValue);
        $query = 'SELECT id FROM cp_members WHERE '.$itemName.'="'.$itemValue.'"
                  AND signup_id != "'.$signupId.'" LIMIT 1';
        $result = DB::query($query);

        // Get number of returned rows
        $num_rows = DB::numRows($result);
        // If the item is not taken
        if (0 == $num_rows) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Creates a subdomain using Cpanel for the member.
     *
     */
    function createSubdomain($subdomainName, $deleteSubdomain = FALSE)
    {
        if (FALSE == $this->mSettings['use_subdomains']) {
            return 'settings[\'use_subdomains\'] was set to FALSE.';
        }
        $port = 2082;
        if (FALSE != $deleteSubdomain) {
            $path = '/frontend/x/subdomain/dodeldomain.html?domain='.$subdomainName.'_'.$this->mSettings['domain']; //or .dll, etc. for authnet, etc.
            $output = '<p>Deleting Subdomain.</p>';
        } else {
            $path = '/frontend/x/subdomain/doadddomain.html?domain='.$subdomainName.'&rootdomain='.$this->mSettings['domain']; //or .dll, etc. for authnet, etc.
            $output = '<p>Adding Subdomain.</p>';
        }
        $authstr = $this->mSettings['cpanel_username'].':'.$this->mSettings['cpanel_password'];
        // Setup the Auth String
        $pass = base64_encode($authstr);
        $fp = fsockopen($this->mSettings['domain'], $port, $errno, $errstr, $timeout = 30);
        if (FALSE == $fp) {
            //error tell us
            $output .= "$errstr ($errno)\n";
        } else {
            //send the server request
            fputs($fp, "POST $path HTTP/1.1\r\n");
            fputs($fp, 'Host: '.$this->mSettings['domain']."\r\n");
            fputs($fp, "Authorization: Basic $pass \r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            // fputs($fp, "Content-length: ".strlen($poststring)."\r\n");
            // fputs($fp, "Content-length: \r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            // fputs($fp, $poststring . "\r\n\r\n");
            // fputs($fp, ''. "\r\n\r\n");
            //*************************************
            // Remove this to stop it from displaying the output fron the  CPanel
            //*************************************
            // loop through the response from the server
            $cpanel_output = '';
            while(FALSE == feof($fp)) {
                $cpanel_output .= fgets($fp, 4096);
            }

            while(FALSE == feof($fp)) {
                fgets($fp, 4096);
            }
            //close fp - we are done with it
            fclose($fp);
        }
        return $output;
    }

    /**
     * Sends a welcome email to members when they register
     *
     * @param $member array - Contains the member's registration info.
     */
    function sendWelcomeEmail($member)
    {
        $from     =  $this->mSettings['email_from'];
        $subject  =  'Account at Example.com created';
        $headers  =  "From: $from\r\n";
        $headers .=  "Reply-To: $from\r\n";
        $headers .=  "X-Mailer: PHP/".phpversion()."\r\n";

        $message  = 'Welcome '.$member['first_name'].",\n\n";
        $message .= 'Your new site, on our server, is now fully ';
        $message .= 'operational and awaits publication!'."\n\n";
        $message .= "------------------------------------------------------\n";
        $message .= "YOUR ACCOUNT INFORMATION\n";
        $message .= "------------------------------------------------------\n\n";
        $message .= 'Folder URL: '.$this->mSettings['site_uri'].'/'.$member['folder_name'].'/'."\n\n";
        $message .= 'Subdomain URL: http://'.$member['folder_name'].'.'.$this->mSettings['domain'].'/'."\n\n";
        $message .= 'Email Address: '.$member['email']."\n\n";
        $message .= 'Login Name: '.$member['login_name']."\n\n";
        $message .= 'Customer Number: '.$member['id']."\n\n";
        // We can't show the password because it is encrypted in the database
        // $message .= 'Password: '.$member['password']."\n\n";
        $message .= "------------------------------------------------------\n";
        $message .= "IMPORTANT INFORMATION\n";
        $message .= "------------------------------------------------------\n\n";
        $message .= 'Control Panel Login:'."\n";
        $message .= 'Using the Control Panel, you can completely administer ';
        $message .= 'your advertising account! Login and check it out.'."\n\n";
        $message .=  $this->mSettings['cp_uri'].'/login.php'."\n\n";
        $message .= 'Login Name: '.$member['login_name']."\n\n";
        $message .= 'Customer Number: '.$member['id']."\n\n";
        $message .= 'Thank you for your order!'."\n\n";

        // Send the email with instructions to reset password
        return mail($member['email'], $subject, $message, $headers );
    }

    /**
     * Activates and sets up the members account
     *
     * @param $signupId int - A 32 char hex string which is the member's
     * unique signup id.
     * @return $status bool - TRUE if account was successfully set up
     *                        FALSE if account setup failed
     */
    function setupAccount($signupId)
    {
        // Get the member information from the database
        $select = array('id', 'email', 'first_name', 'folder_name', 'num_pages',
                        'login_name');
        $where = 'WHERE signup_id="'.$signupId.'"';
        $result = DB::select('cp_members', $select, $where);
        $member = DB::fetchAssoc($result);

        // Check to see if the member already has their pages in the database
        $query = 'SELECT id FROM cp_member_pages WHERE member_id="'.$member['id'].'"';
        $result = DB::query($query);
        $num_current_pages =  DB::numRows($result);

        // Create member's pages in the database if they don't already have them
        for ($page_number = $num_current_pages + 1; $page_number <= $member['num_pages']; $page_number++) {
            $query2 = 'INSERT INTO cp_member_pages ( `id` , `member_id` , `page_number`)
                       VALUES ("", "'.$member['id'].'", "'.$page_number.'");';
            DB::query($query2);
        }

        // Activate the member's account so that they can login to the CP
        $query3 = 'UPDATE cp_members SET account_active="1"
                   WHERE signup_id="'.$signupId.'"';
        DB::query($query3);

        // Create the member's subdomain
        $this->createSubdomain($member['folder_name']);

        $this->createMemberFolder($member['folder_name']);

        // The account has been set up so we need to update the database
        $query4 = 'UPDATE cp_members SET account_setup="1" WHERE signup_id="'.$signupId.'"';
        DB::query($query4);

        // Send the member a email with a welcome and instructions
        $this->sendWelcomeEmail($member);

        // TODO: make $status be false if something went wrong
        $status = TRUE;

        return $status;
    }

    /**
     * Creates member's folder
     *
     */
    function createMemberFolder($folderName)
    {
        // Make the member's folder if it does not exist
        if (FALSE == is_dir(dirname(__FILE__).'/../../'.$folderName.'/')) {
            mkdir(dirname(__FILE__).'/../../'.$folderName.'/');
        }

        /* TODO Add error checking */
        $chmod_result = $this->makeMemberFolderWritable($folderName);
        // echo $chmod_result;
        // mail('elijah@truthland.com', 'CHMOD Result', $chmod_result, '');

        // Copy the default .htaccess file which rewrites extensionless
        // requests to index.php
        copy(dirname(__FILE__).'/../../site/pages/default.htaccess',
             dirname(__FILE__).'/../../'.$folderName.'/.htaccess');
        // Copy the default index.php which includes show-page.php
        copy(dirname(__FILE__).'/../../site/pages/default_index.php',
             dirname(__FILE__).'/../../'.$folderName.'/index.php');
        // Copy the default 404-error.php which handles and logs 404 errors
        copy(dirname(__FILE__).'/../../site/pages/default_404-error.php',
             dirname(__FILE__).'/../../'.$folderName.'/404-error.php');
        // Copy the default favicon.ico file for the subdomain
        copy(dirname(__FILE__).'/../../site/pages/default_favicon.ico',
             dirname(__FILE__).'/../../'.$folderName.'/favicon.ico');
    }

    /**
     * Makes a member folder writable by chmodding it to 0777
     *
     * This method logs into the ftp server and chmods the member's folder
     * to 0777 so that our php scripts can write to it
     */
    function makeMemberFolderWritable($folderName)
    {
        $output = '';
        if (FALSE != $this->mSettings['use_ftp']) {
            $perms = '0777';
            $connection = ftp_connect($this->mSettings['ftp_server']);
            if (FALSE == $connection) {
                $output .= 'Could not connect to ftp server: '.$this->mSettings['ftp_server']."\n";
            } else {
                $login_result = ftp_login($connection, $this->mSettings['ftp_username'], $this->mSettings['ftp_password']);
                if (FALSE == $login_result) {
                    $output .= 'Could not login to ftp server: '.$this->mSettings['ftp_server']."\n";
                } else {
                    // Change to the ftp path (most likely /public_html/)
                    if (FALSE == ftp_chdir($connection, $this->mSettings['ftp_path'])) {
                        $output .= 'Could not change to dir ('.$this->mSettings['ftp_path'].')'."\n";
                    }
                    if (FALSE == ftp_site($connection, 'CHMOD '.$perms.' '.$folderName)) {
                        $output .= 'CHMOD failed.';
                    } else {
                        $output .= 'CHMOD successful';
                    }
                }
            }
        } else {
            $output = 'use_ftp was set to FALSE. Folder not chmodded.';
        }
        return $output;
    }

    /**
     * Deletes a member's account and all their files
     * @todo : Delete member's pages, pictures and account from database.
     */
    function delete($memberId)
    {
        // $Member = new Member('cp', $this->mSettings, $memberId);
        // Get the folder name of the member
        // $folder_name = $Member->getAttribute('folder_name');


        $select = array('folder_name');
        $where = 'WHERE id="'.$memberId.'"';
        $result = DB::select('cp_members', $select, $where);
        $member = DB::fetchAssoc($result);

      //  $folder = dirname(__FILE__).'/../../'.$member['folder_name'].'/';

        // Delete the member's subdomain
        $delete_subdomain = TRUE;
        $output = $this->createSubdomain($member['folder_name'], $delete_subdomain);

        // Delete the member's folder
      /*
        if (FALSE == empty($member['folder_name'])) {
            if (FALSE != is_dir($folder)) {
                $output .= $this->FtpRecursiveDelete($member['folder_name']);
            } else {
                $output .= 'Error: Folder does not exist. Not deleting: '.$member['folder_name'];
            }
            // $output = $this->deleteFolder($folder, $member['folder_name']);
        } else {
         $output .= 'Error: Member does not have valid folder name. ('.$member['folder_name'].')';
            echo $output;
            die();
        }
        */

        $output .= '<p><b>Deleting members pages, pictures, and account from the database</b></p>';
        $output .= '<ul>';
        // Delete member's pages from the database
        DB::delete('cp_member_pages', 'member_id="'.$memberId.'"', 0);
        $output .= '<li>Deleted member&rsquo;s pages from the from database.</li>';
        // Delete member's pictures from the database
        DB::delete('cp_member_pictures', 'member_id="'.$memberId.'"', 0);
        $output .= '<li>Deleted member&rsquo;s pictures from database.</li>';
        // Deactivate member's account
        $update = array('account_active' => 0);
        DB::update ('cp_members', $update, 'id="'.$memberId.'"');
        // DB::delete('cp_members', 'id="'.$memberId.'"', 0);
        $output .= '<li>De-activated member&rsquo;s account.</li>';
        $output .= '</ul>';
        $output .= '<p style="color:red; font-size: 3em;">Please manually ';
        $output .= 'delete the members folder (/'.$member['folder_name'].'/) ';
        $output .= 'now!</p>';
        return $output;
    }

   
}
?>
