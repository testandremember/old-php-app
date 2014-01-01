<?php
/**
 * Performs member related functions for the Control Panel
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Member.class.php 1940 2005-05-24 22:33:52Z elijah $
 * @package NiftyCMS
 */
// The length of the password reset key
define ('KEY_LENGTH', 12);

class Member {
    // The type of member. 'admin' or 'cp'
    public $mType = '';
    // An array of the site settings
    private $mSettings = array();
    // The Member Id
    private $mId = 0;
    // An array of member names
    private $mNames = array();
    /**
     * Initializes the class and sets the Member Type.
     *
     * @param int $memberId - Optionally set the id of the member.
     */
    function Member($memberType, $settings, $memberId = 0)
    {
        $this->mType = $memberType;
        $this->mSettings = $settings;
        $this->mId = $memberId;
    }
    /**
     * Log the member in if the supply the correct information.
     *
     * The supplied username and password are checked against the database.
     * If the member supplied valid information a cookie is set containing their session id.
     * If invalid information is supplied then the user is sent back to the login page.
     */
    function authenticate (&$formObject)
    {
        // Encrypt the user submitted password using the md5() function so that
        // we can compare it the encrypted password stored in the database
        $encrypted_password = md5($formObject->request['password']);

        // $query = 'SELECT id, password, account_active FROM '.$this->mType.'_members WHERE email="'.$formObject->request['email'].'" LIMIT 1';
        $query = 'SELECT id, password, account_active, login_name
                  FROM '.$this->mType.'_members
                  WHERE login_name="'.$formObject->request['login_id'].'"
                  OR id="'.$formObject->request['login_id'].'" LIMIT 1';
        // echo $query;
        $result = DB::query($query);
        $member = DB::fetchAssoc($result);
        $num_rows = DB::numRows($result);

        // If the submitted username matches one in the database
        if (1 == $num_rows) {
            if (1 == $member['account_active']) {
                // If the submitted password does not match the one in the database
                if ($encrypted_password != $member['password']) {
                    // Log the incorrect passsword event
                    Event::logEvent(24, $member['id'], $this->mType, '');
                    // You entered an incorrect password
                    $formObject->setError('password', 24);
                }
            } else {
                $formObject->setError('login_id', 37);
            }
        } else {
            // If there is no matching login name or customer number
            $formObject->setError('login_id', 65);
        }

        // If there are no errors
        if (FALSE == $formObject->HasErrors()) {
            $this->logMemberIn($member['id']); // Log the member in
            if (FALSE == isset($_GET['from'])) {
                // Send them to the Panel index page
                header('Location: '.$this->mSettings[$this->mType.'_uri'].'/?event=38');
            } else {
                $from_uri = urldecode($_GET['from']);
                header('Location: '.$from_uri);
            }
        }
    }

    /**
     * Logs the member in
     *
     */
    function logMemberIn($member_id)
    {
        // Log the login event
        Event::logEvent(38, $member_id, $this->mType, '');

        $num_rows = 1;
        while (0 != $num_rows) {
            // Generate a unique token to be used for this session
            $token = md5(uniqid($member_id.rand(), TRUE));
            $query = 'SELECT id FROM '.$this->mType.'_member_sessions
                      WHERE session_id="'.$token.'" LIMIT 1';
            $result = DB::query($query);
            //        $member=DB::fetchAssoc($result);
            $num_rows = DB::numRows($result);
        }

        // Set a cookie on the member's pc so that we can identify them
        setcookie($this->mType.'_session_id', $token, NULL, '/');

        $user_agent = md5($_SERVER['HTTP_USER_AGENT']);
        // Add the user's session into the database
        $query = 'INSERT INTO '.$this->mType.'_member_sessions
                  (id, session_id, user_agent, member_id, ip, last_action)
                  VALUES ("", "'.$token.'", "'.$user_agent.'", "'.$member_id.'",
                          "'.$_SERVER['REMOTE_ADDR'].'", "'.time().'")';
        DB::query($query);
    }

    /**
     * Checks if the user is logged in
     *
     */
    function isLoggedIn(&$reason)
    {
        if (FALSE == isset($_COOKIE[$this->mType.'_session_id'])) {
            // No session cookie
            $reason = 33;
            // Event::logEvent($reason, 0, '');
            return FALSE;
        }
        $this->mSessionId = DB::makeSafe($_COOKIE[$this->mType.'_session_id']);
        $query = 'SELECT member_id, user_agent, last_action
                  FROM '.$this->mType.'_member_sessions
                  WHERE session_id = "'.$this->mSessionId.'"';
        $result = DB::query($query);
        $member = DB::fetchAssoc($result);
        // Encode the useragent with md5
        $user_agent = md5($_SERVER['HTTP_USER_AGENT']);
        if ($member['user_agent'] != $user_agent) {
            // Wrong user agent
            $reason = 42;
            return FALSE;
        }
        $one_hour_ago = mktime(date("H") - 1, date("i"), date("s"),
                               date("m"), date("d"), date("Y"));
        // $one_minute_ago = mktime(date("H"), date("i") - 1, date("s"), date("m"), date("d"), date("Y"));
        if ($member['last_action'] < $one_hour_ago) {
            // Session timed out
            $reason = 32;
            return FALSE;
        }
        $this->mId = $member['member_id'];
        return TRUE;
    }

    /**
     * Sends an email with instructions for the member to reset their password
     *
     */
    function sendPasswordResetEmail(&$formObject)
    {
        $email_to = $formObject->request['email'];
        $query = 'SELECT id,first_name FROM '.$this->mType.'_members
                  WHERE email = "'.$email_to.'" LIMIT 1';
        $result   = DB::query($query);
        $num_rows = DB::numRows($result);
        $member   = DB::fetchAssoc($result);
        if (1 == $num_rows) {
            while (0 != $num_rows) {
                // Generate a unique token to be used for this session
                $key = substr(md5(time()), 0, KEY_LENGTH);
                $query = 'SELECT id FROM '.$this->mType.'_password_resets
                          WHERE reset_key="'.$key.'" LIMIT 1';
                $result = DB::query($query);
                $num_rows = DB::numRows($result);
            }
            // Email the member instructions on resetting their password
            $mail_result = $this->_emailInstructions($email_to, $member['first_name'], $key);
            // If sending email was successful
            if (FALSE != $mail_result) {
                // Password reset will expire in 1 day
                $expires = mktime(date("H"), date("i"), date("s"),
                                  date("m"), date("d")+1, date("Y"));
                $query = 'INSERT INTO '.$this->mType.'_password_resets
                          (id, reset_key, member_id, expires)
                          VALUES ("", "'.$key.'", "'.$member['id'].'",
                                  "'.$expires.'")';
                DB::query($query);
                return TRUE; // Sending email was successful
            } else {
                return FALSE; // Sending email failed
            }
        } else {
            // Error: That email address does not exist in our database.
            $formObject->setError('email', 28);
            return FALSE; // Sending email failed
        }
    }

    /**
     * Emails the instructions on how to reset the password
     *
     * @author Elijah Lofgren <elijah@truthland.com>
     */
    function _emailInstructions($to, $member_name, $key)
    {
        $from     =  $this->mSettings['email_from'];
        $subject  =  'Password reset instructions';
        $headers  =  "From: $from\r\n";
        $headers .=  "Reply-To: $from\r\n";
        $headers .=  "X-Mailer: PHP/".phpversion()."\r\n";

        $message = 'Hello '.$member_name.",\n\n";
        $message  .=  "To reset your password please visit the following page:\n";
        $message .=  $this->mSettings[$this->mType.'_uri'].'/reset-password.php?key='.$key."\n\n";
        $message .= "If you need further help, please don't hesitate to \n";
        $message .= "contact us about your problem by replying to this email.";
        $message .= "\n\nThanks,\n";
        $message .= "The support team\n";

        // Send the email with instructions to reset password
        return mail( $to, $subject, $message, $headers );
    }

    /**
     * Logs the member out
     *
     */
    function logOut()
    {
        // Log the logout event
        Event::logEvent(5, $this->mId, $this->mType, '');
        // Delete the session cookie
        setcookie ($this->mType.'_session_id', '', (time () - 2592000), '/', '', 0);
    }

    /**
     * Returns the id of the member
     *
     */
    function getId()
    {
        return (int) $this->mId;
    }

    /**
     * Returns a given attribute of member
     *
     */
    function getAttribute($attributeName)
    {
        if (0 != $this->mId) {
            $query = 'SELECT '.$attributeName.' AS attribute
                      FROM '.$this->mType.'_members
                      WHERE id="'.$this->mId.'" LIMIT 1';
            $result = DB::query($query);
            $member = DB::fetchAssoc($result);
            $attribute = $member['attribute'];
        } else {
            $attribute = '';
        }

        return $attribute;
    }

    /**
     * Returns the URI of the member's site
     *
     */
    function getSiteUri($cpMemberId = 0)
    {
        if ($this->mType != 'cp' && FALSE != empty($cpMemberId)) {
            die('Error: $Member->getSiteUri() can only be called for "cp" member objects if the $cpMemberId parameter is passed.');
        }

        $select = array('folder_name', 'domain_name');
        if (FALSE == empty($cpMemberId)) {
            $member_id = $cpMemberId;
        } else {
            $member_id = $this->mId;
        }
        $where = 'WHERE id="'.$member_id.'"';
        $result = DB::select('cp_members', $select, $where);
        $member = DB::fetchAssoc($result);

        $output = 'http://';

        if (FALSE == empty($member['domain_name'])) {
            $output .= $member['domain_name'];
        } else {
            $output .= $member['folder_name'].'.'.$this->mSettings['domain'];
        }
        $output .= '/';
        return $output;
    }

    /**
     * Checks to see if a member can upload more images.
     *
     */
    function canUploadMoreImages($memberId, &$numLeft)
    {
        $query = 'SELECT num_pictures FROM '.$this->mType.'_members
                  WHERE id="'.$memberId.'" LIMIT 1';
        $result = DB::query($query);
        $member = DB::fetchAssoc($result);

        $query2 = 'SELECT id FROM '.$this->mType.'_member_pictures
                   WHERE member_id="'.$memberId.'" ORDER BY id ASC';
        $result2 = DB::query($query2);
        $num_rows = DB::numRows($result2);

        // The number of images left that the member can upload
        $numLeft = $member['num_pictures'] - $num_rows;

        if ($num_rows < $member['num_pictures']) {
            // Member can upload more images
            return TRUE;
        } else {
            // Member can not upload any more images
            return FALSE;
        }
    }

    /**
     * Updates the database with the last action of the member so that they
     * can be logged out automatically if they are inactive
     *
     */
    function updateLastAction()
    {
        $query = 'UPDATE '.$this->mType.'_member_sessions
                  SET last_action="'.time().'"
                  WHERE session_id="'.$this->mSessionId.'" LIMIT 1';
        DB::query($query);
    }

    /**
     * Deletes a member's image file if it is not in use
     *
     * This method checks the database to make sure that the pictures is not
     * being used as a logo, picture, or thumbnail. Then if unused it deletes
     * the image file.
     */
    function deleteImage($filename) {
        if ($this->mType != 'cp') {
            die('Error: $Member->deleteImage() can only be called for "cp" member objects.');
        }
        $path = dirname(__FILE__).'/../../'.$this->getAttribute('folder_name').'/';
        // Only delete the picture if it is a valid file
        if ('' != $filename && TRUE == is_file($path.$filename)) {
            // Make sure that the file is not used else where
            $query = 'SELECT id FROM cp_member_pictures
                      WHERE file_name="'.$filename.'"
                      AND member_id="'.$this->mId.'" LIMIT 1';
            $result = DB::query($query);
            $num_picture_rows = DB::numRows($result);

            $query2 = 'SELECT id FROM cp_member_pictures
                       WHERE thumbnail_name="'.$filename.'"
                       AND member_id="'.$this->mId.'" LIMIT 1';
            $result2 = DB::query($query2);
            $num_thumbnail_rows = DB::numRows($result2);


            $query3 = 'SELECT id FROM cp_member_pages WHERE logo="'.$filename.'"
                       AND member_id="'.$this->mId.'" LIMIT 1';
            $result3 = DB::query($query3);
            $num_logo_rows = DB::numRows($result3);

            // Delete the picture is it is not being used as a logo, picture, or thumbnail
            if (0 == $num_picture_rows && 0 == $num_thumbnail_rows && 0 == $num_logo_rows) {
                if (FALSE != is_file($path.$filename)) {
                    // Delete the image.
                    unlink($path.$filename);
                    Event::logEvent(44, $this->mId, $this->mType, $path.$filename);
                }
            }
        } else {
            Event::logEvent(93, $this->mId, $this->mType, $path.$filename);
        }
    }

    /**
     * Gets a member's name from the DB if it has not already been fetched.
     *
     */
    function getName($memberId, $memberType)
    {
        if (FALSE == isset($this->mNames[$memberType][$memberId])) {
            $select = array('first_name', 'last_name');
            $where = 'WHERE id="'.$memberId.'"';
            $result2 = DB::select($memberType.'_members', $select, $where);
            $member_data = DB::fetchAssoc($result2);

            $this->mNames[$memberType][$memberId] = $member_data;
            // print_r($this->mNames);
        }
        $output = $this->mNames[$memberType][$memberId]['first_name'].' ';
        $output .= $this->mNames[$memberType][$memberId]['last_name'];
        return $output;

    }

    /**
     * Checks to see if an item of a member's account already taken such as
     * their login name or email address.
     *
     * @param $itemName string - The name of the item being checked
     * @param $itemValue string - The value of the item being checked
     * @return bool - TRUE if email is taken. FALSE if email is unused.
     */
    function isAttributeInUse($itemName, $itemValue)
    {
        $itemValue = DB::MakeSafe($itemValue);

//       $query = 'SELECT id FROM '.$this->mType.'_members WHERE '.$itemName.'="'.$itemValue.'"
  //                AND id != "'.$signupId.'" LIMIT 1';

        $select = array('id');
        $where = ' WHERE '.$itemName.'="'.$itemValue.'" AND id != "'.$this->mId.'"';
        $result = DB::select($this->mType.'_members', $select, $where);

        // Get number of returned rows
        $num_rows = DB::numRows($result);
        // If the item is not taken
        if (0 == $num_rows) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
?>
