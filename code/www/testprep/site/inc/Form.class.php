<?php
/**
 * Checks data to see if it is valid.
 *
 * If you do use this software and find it useful, I would
 * love to know. Or if you find a bug. Email: <kembu@hotmail.com>
 * @author: Kevin Burke <kembu@hotmail.com>
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Form.class.php 1940 2005-05-24 22:33:52Z elijah $
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package NiftyCMS
 *
 */
class Form {
    private $errors = array();
    public $request = array();
    // Values that are preset for the form fields
    private $presetValues = array();
    // Type of member (used for error logging)
    private $mMemberType = 'guest';
    // An array of settings
    private $mSettings = array();
    // Whether the htmlArea JS code has been included
    private $mHtmlAreaIncluded = FALSE;
    /**
     * Instantiates the Form class.
     *
     */
    function Form($requestArray, $presetValues, $memberType = 'guest', $settings = array())
    {
        if (FALSE == is_array($requestArray)) {
            $requestArray = array();
            trigger_error('$requestArray parameter must be an array.', E_USER_NOTICE);
        }
        if (FALSE == is_array($presetValues)) {
            $presetValues = array();
            trigger_error('$presetValues parameter must be an array.', E_USER_NOTICE);
        }
        $this->request = $requestArray;
        $this->presetValues = $presetValues;
        $this->mMemberType = $memberType;
        $this->mSettings = $settings;
    }

    /**
     * Checks to makes sure that a form field is filled in
     *
     */
    function isFilledIn($field)
    {
        // If it is filled in
        // if (FALSE != array_key_exists($field, $this->request) && '' != $this->request[$field]) {
        if (FALSE == empty($this->request[$field])) {
            return TRUE;
        } else {
            // Set the error 'You must fill in this field.'
            $this->SetError($field, 14);
            return FALSE;
        }
    }

    /**
     * Checks to makes sure that a checkbox is checked
     *
     */
    function isChecked($field)
    {
        // If it is filled in
        if (FALSE != array_key_exists($field, $this->request) && '' != $this->request[$field]) {
            return TRUE;
        } else {
            // Set the error 'This box must be checked.'
            $this->SetError($field, 62);
            return FALSE;
        }
    }


    /**
     * Checks make sure that an option was chosen from a drop-down box
     *
     */
    function hasPickedOption($field)
    {
        // If it is filled in
        if (FALSE != array_key_exists($field, $this->request) && '' != $this->request[$field]) {
            return TRUE;
        } else {
            // Set the error 'You must choose an option from the drop-down box.'
            $this->SetError($field, 15);
            return FALSE;
        }
    }

    /**
     * Checks make sure that a radio option was selected
     *
     */
    function hasPickedRadioOption($field)
    {
        // If it is filled in
        if (FALSE != array_key_exists($field, $this->request) && '' != $this->request[$field]) {
            return TRUE;
        } else {
            // Set the error 'You must choose an option from the drop-down box.'
            $this->SetError($field, 54);
            return FALSE;
        }
    }

    /**
     * Checks a string to see if it is in valid email address format
     *
     */
    function isEmail($field)
    {
        if (FALSE != $this->IsFilledIn($field)) {
            $address = trim($this->request[$field]);
            if (ereg('^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$', $address)) {
                return TRUE;
                // return '';
            } else {
                // Error 'You entered an invalid email address.'
                $this->SetError($field, 16);
                return FALSE;
                // return '<tr><td colspan="2" class="error">You entered an invalid email address.</td></tr>';
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Checks a string to see if it is a valid integer
     *
     */
    function isInteger($field)
    {
        if (FALSE != $this->isFilledIn($field)) {
            if (FALSE != ereg ("^[0-9]+$", $this->request[$field])) {
                return TRUE;
                // return '';
            } else {
                // Error 'You entered an invalid number.'
                $this->SetError($field, 63);
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Checks to make sure that the phone number is in the format 000-000-0000
     *
     */
    function isPhoneNumber($field, $optional)
    {
        // If the field is optional and empty then its valid
        if (FALSE != $optional && '' == $this->request[$field]) {
            return TRUE;
        } else {
            if (FALSE != ereg ("([0-9]{3})-([0-9]{3})-([0-9]{4})", $this->request[$field])) {
                return TRUE;
            } else {
                // Phone Number must be in 000-000-0000 format
                $this->SetError($field, 13);
                return FALSE;
            }
        }
    }

    /**
     * Checks to make sure that the phone number is in the format 000-000-0000
     *
     */
    function isZipCode($field)
    {
        // If in the format 12345
        if (FALSE != ereg ("([0-9]{5})", $this->request[$field]) && 5 == strlen($this->request[$field])) {
            return TRUE;
        } else {
            // If in the format 12345-1234
            if (FALSE != ereg ("([0-9]{5})-([0-9]{4})", $this->request[$field])) {
                return TRUE;
            } else {
                // Zip code must be in 12345 or 12345-1234 format
                $this->SetError($field, 17);
                return FALSE;
            }
        }
    }

    /**
     * Checks to see if two fields are equal (useful for confirming passwords)
     *
     */
    function areEqual($field1, $field2)
    {
        if (FALSE != $this->isFilledIn($field1) && FALSE != $this->isFilledIn($field2)) {
            if (0 == strcmp($this->request[$field1], $this->request[$field2])) {
                return TRUE;
            } else {
                //            $this->setError($field1, 103, ');
                $this->setError($field2, 18);
                // $this->SetError($field2, 18);
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    function isValidPassword($field, $value)
    {
        if (FALSE != ereg("[[:punct:]]", $value) OR FALSE != ereg("[[:space:]]", $value)) {
            // Your password must not contain any punctuation or spaces.
            $this->SetError($field, 19);
            return FALSE;
        } else {
            if (strlen($value) <= 50 AND strlen($this->request[$field]) >= 6) {
                return TRUE;
            } else {
                // Your password must be at least 6 characters long and shorter than 50 characters.
                $this->SetError($field, 20);
                return FALSE;
            }
        }
    }

    /**
     * Checks to make sure that a string only contains letters and numbers
     *
     * @param $field string - The name of the field being tested
     * @return bool - TRUE or FALSE depending on whether
     * the format is good or bad
     */
    function isAlphanumeric($field)
    {
        if (FALSE == empty($this->request[$field])) {
            // Make that string only contains letters and numbers
            if (FALSE == ereg("^[a-zA-Z0-9]+$", $this->request[$field])) {
                // This field must contain only letters and numbers.
                $this->setError($field, 64);
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            // This field must contain only letters and numbers.
            $this->setError($field, 64);
            return FALSE;
        }
    }

    /**
     * Checks to make sure that a folder name only contains: letters, numbers and
     * dashes.
     *
     * @param $field string - The name of the field being tested
     * @param $value string - The value of the field
     * @return bool - TRUE or FALSE depending on whether
     * the format is good or bad
     */
    function isValidDirName($field, $value)
    {
        // Make sure that there are no spaces
        if (FALSE == ereg("^[a-zA-Z0-9-]+$", $value)) {
            // Your folder name must only contain letters, numbers, and dashes.
            $this->SetError($field, 21);
            return FALSE;
        } else {
            $length = strlen($value);
            if ($length <= 100) {
                return TRUE;
            } else {
                // Your folder name must not be more than 100 characters long.
                $this->SetError($field, 22);
                return FALSE;
            }
        }
    }

    /**
     * Called by methods of the Form class to set an error on a field
     *
     */
    function setError($field, $error_id)
    {
        if (FALSE == empty($this->request[$field])) {
            // Log the error to the database
            Event::logEvent($error_id, 0, $this->mMemberType,
                            $this->request[$field]);
        } else {
            // Log the error to the database
            Event::logEvent($error_id, 0, $this->mMemberType, $field);
        }
        $this->errors[$field][] = $error_id;
        // print_r($this->errors);
    }

    /**
     * Returns TRUE or FALSE depending on whether there were form input errors
     *
     */
    function hasErrors()
    {
        //   $number_of_errors = count($this->errors);
        $number_of_errors = count($this->errors);
        if (0 == $number_of_errors) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Returns the text of the first error encountered for a given form element
     *
     */
    function getError($name)
    {
        /*     echo '<pre>';
        print_r($this->errors);
        echo '</pre>'; */
        /*  foreach ($this->errors[$name] as $key => $value) {
            $error_text =  '<tr><td colspan="2" class="error">'.$value."</td></tr>\n";
        } */
        /*       echo '<pre style="text-align:left">';
        print_r($this->errors);
        echo '</pre>'; */
        $error_text = Event::getMessage($this->errors[$name][0]);
        // return $this->errors[$name][0]['text'];
        return $error_text;
    }

    /**
     * Returns TRUE or FALSE depending on whether there is an error for a
     * certain form element.
     *
     */
    function hasError($name)
    {
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            if (FALSE != array_key_exists($name, $this->errors)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Starts a form
     *
     */
    function startForm($actionTarget, $type = 'post')
    {
        // Note: we need to use enctype="multipart/form-data" for file uploads
        // and to fix a php bug where form data was getting corrupted
        $output  = '<form action="'.$actionTarget.'" ';
        $output .= 'enctype="multipart/form-data" method="'.$type.'" id="form1">'."\n";
        return $output;
    }

    /**
     * Ends a form
     *
     */
    function endForm()
    {
        return '</form>';
    }

    /**
     * Starts a form fieldset
     *
     */
    function startFieldset ($legend)
    {
        return '<fieldset><legend>'.$legend.'</legend>'."\n".'<table>'."\n";
    }

    /**
     * Ends a form fieldset
     *
     */
    function endFieldset()
    {
        return '</table></fieldset>';
    }

    /**
     * Shows an information box
     *
     */
    function showInfo ($info)
    {
        return '<tr><td colspan="2" class="infobox">'.$info."</td></tr>\n";
    }

    /**
     * Shows a form textarea
     *
     */
    function showTextarea ($label, $name, $rows = 30, $richText = FALSE,
                           $pageStyle = '', $submitOnEnter = FALSE)
    {
        if (FALSE != $this->hasError($name)) {
            $error = $this->getError($name).'<br />';
            $highlight = ' class="error"';
        } else {
            $highlight = '';
            $error = '';
        }

        $output  = '<tr><td colspan="2"><label'.$highlight.' for="'.$name.'">';
        $output .= $label.': </label></td></tr>'."\n";
        $output .= '<tr><td colspan="2"'.$highlight.'>'.$error."\n";
        if (FALSE != array_key_exists($name, $this->presetValues)) {
            $value = $this->presetValues["$name"];
        } else {
            $value = '';
        }
        $output .= '<textarea rows="'.$rows.'" cols="55" name="'.$name.'" ';
        if (FALSE != $submitOnEnter) {
            $output .= 'onkeypress="if(event.keyCode==13)document.getElementById(\'form1\').submit();" ';
        }
        $output .= 'id="'.$name.'">'.$value.'</textarea>'."\n";
        if (FALSE != $richText) {
            if (FALSE == $this->mHtmlAreaIncluded) {
                $this->mHtmlAreaIncluded = TRUE;
                $output .= '<script type="text/javascript" src="'.$this->mSettings['inc_uri'].'/FCKeditor/fckeditor.js"></script>'."\n";
            }
/*
            $output .= '<script type="text/javascript">'."\n";
            $output .= 'var '.$name.'oFCKeditor = new FCKeditor( \''.$name.'\' ) ;'."\n";
            $output .= $name.'oFCKeditor.BasePath = "'.$this->mSettings['inc_uri'].'/FCKeditor/" ;'."\n";
            $output .= $name.'oFCKeditor.Height = 400 ; // 400 pixels'."\n";
            // $output .= $name.'oFCKeditor.Value = \''.$value.'\' ;'."\n";
            $output .= $name.'oFCKeditor.Create() ;'."\n";
            $output .= '</script>'."\n";
            */

            $output .= '<script type="text/javascript">'."\n";
            $output .= 'var '.$name.'oFCKeditor = new FCKeditor( \''.$name.'\' ) ;'."\n";
            $output .= $name.'oFCKeditor.BasePath = "'.$this->mSettings['inc_uri'].'/FCKeditor/" ;'."\n";
            $output .= $name.'oFCKeditor.Config[ "ImageBrowser" ] = false ;'."\n";
            $output .= $name.'oFCKeditor.Config[ "LinkBrowser" ] = false ;'."\n";
            $output .= $name.'oFCKeditor.Config[ "StartupFocus" ] = false ;'."\n";
            // $output .= 'oFCKeditor.Value = "'.$value.'" ;'."\n";
            $output .= $name.'oFCKeditor.Height = 400 ; // 400 pixels'."\n";
            $output .= $name.'oFCKeditor.ReplaceTextarea() ;'."\n";
            $output .= '</script>'."\n";

            /*
                $output .= '<script type="text/javascript">'."\n";
                $output .= '_editor_url = "'.$this->mSettings['inc_uri'].'/htmlarea/";'."\n";
                $output .= '_editor_lang = "en";'."\n";
                $output .= '</script>'."\n";
                $output .= '<script type="text/javascript" ';
                $output .= 'src="'.$this->mSettings['inc_uri'].'/htmlarea/htmlarea.js">';
                $output .= '</script>'."\n";
            }
            $output .= '<script type="text/javascript" defer="defer">'."\n";
            // create a new configuration object having all the default values
            $output .= 'var '.$name.' = new HTMLArea.Config();'."\n";
            // the following sets a style for the page body
            $output .= $name.'.pageStyle = \''.$pageStyle.'\';'."\n";
            // the following replaces the textarea with the given id with a new
            // HTMLArea object having the specified configuration
            $output .= 'HTMLArea.replace(\''.$name.'\', '.$name.');'."\n";
           // $output .= 'HTMLArea.replaceAll();';
            // create a new configuration object
            // $output .= 'var config = new HTMLArea.Config();'."\n";
            // having all the default values
            // the following sets a style for the page body
            // $output .= 'config.pageStyle = \''.$pageStyle.'\';'."\n";
            //  \'body { background-color: yellow; color: orange; } \' +
            // \'a:visited { color: green; } \';
            // the following replaces the textarea with the given id with a new
            // HTMLArea object having the specified configuration
             // $output .= 'HTMLArea.replace(\''.$name.'\', config);'."\n";
            $output .= '</script>'."\n";
            */
        }
        $output .= '</td></tr>'."\n";
        return $output;
    }

    /**
     * Shows some text
     *
     */
    function showText($label, $value)
    {
        $result = '<tr><td><b>'.$label.":</b></td>\n";
        $result .= '<td>'.$value."</td></tr>\n";
        return $result;
    }

    /**
     * Shows a link
     *
     */
    function showLink($title, $uri)
    {
        $output  = '<tr><td colspan="2" class="formlink">';
        $output .= '<a href="'.$uri.'">'.$title."</a></td></tr>\n";
        return $output;
    }

    /**
     * Shows a text input
     *
     */
    function showTextInput($label, $name, $maxLength, $size = 0, $showAfter = '')
    {
        if (FALSE != $this->HasError($name)) {
            $error = '<br />'.$this->getError($name);
            $highlight = ' class="error"';
        } else {
            $highlight = '';
            $error = '';
        }
        $output  = '<tr><td>';
        $output .= '<label for="'.$name.'"'.$highlight.'>'.$label.': </label>';
        $output .= '</td>'."\n";
        if (FALSE != array_key_exists($name, $this->presetValues)) {
            $value = ' value="'.$this->presetValues["$name"].'"';
        } else {
            $value = '';
        }
        //    }
        if (FALSE != empty($size)) {
            $width = ' class="default"';
        } else {
            $width = ' size="'.$size.'"';
        }
        if (0 == $maxLength) {
            $length = '';
        } else {
            $length = ' maxlength="'.$maxLength.'"';
        }

        $output .= '<td'.$highlight.'>';
        $output .= '<input type="text" name="'.$name.'" id="'.$name.'"';
        $output .= $value.$width.$length.' />'."\n";
        if (FALSE != empty($error)) {
            $output .= $showAfter;
        }
        $output .= $error.'</td></tr>'."\n";
        return $output;
    }

    /**
     * Shows a password input
     *
     */
    function showPasswordInput($label, $name, $showAfter = '')
    {
        if (FALSE != $this->HasError($name)) {
            $error = '<br />'.$this->getError($name);
            $highlight =' class="error"';
        } else {
            $error = '';
            $highlight='';
        }

        $output  = '<tr><td>';
        $output .= '<label for="'.$name.'"'.$highlight.'>'.$label.': </label>';
        $output .= '</td>'."\n";
        $output .= '<td'.$highlight.'>';
        $output .= '<input type="password" name="'.$name.'" id="'.$name.'" ';
        $output .= 'maxlength="50" class="default" />';
        $output .= $error.$showAfter.'</td></tr>'."\n";
        return $output;
    }

    /**
     * Shows a checkbox
     *
     */
    function showCheckbox($label, $name, $checkedValue, $showAfter = '')
    {
        if (FALSE != $this->hasError($name)) {
            $error = '<br />'.$this->getError($name);
            $highlight = ' class="error"';
        } else {
            $error = '';
            $highlight = '';
        }
        if (FALSE != array_key_exists($name, $this->presetValues)
            && $this->presetValues[$name] == $checkedValue) {
            $checked = ' checked="checked"';
        } else {
                $checked = '';
            }
        $output = '<tr><td>';
        $output .= '<label for="'.$name.'"'.$highlight.'>'.$label.': </label>';
        $output .= '</td>'."\n";
        $output .= '<td'.$highlight.'>';
        $output .= '<input type="checkbox" name="'.$name.'" id="'.$name.'" ';
        $output .= $checked.'value="'.$checkedValue.'" />';
        if (FALSE == empty($showAfter)) {
            $output .= '<span class="showafter">'.$showAfter.'</span>';
        }
        $output .= $error.'</td></tr>'."\n";
        return $output;
    }


    /**
     * Shows an input that lets user select a file to upload
     *
     */
    function showFileInput($label, $name)
    {
        if (FALSE != $this->hasError($name)) {
            $error = $this->getError($name);
            $highlight = ' class="error"';
        } else {
            $error = '';
            $highlight = '';
        }
        $output  = '<tr><td><label for="'.$name.'">'.$label.": </label></td>\n";
        $output .= '<td'.$highlight.'>';
        $output .= '<input type="file" name="'.$name.'" id="'.$name.'" />';
        $output .= $error.'</td></tr>'."\n";
        return $output;
    }

    /**
     * Shows a form submit button
     *
     */
    function showSubmitButton($label, $cancelUri = FALSE, $moreButtons = array())
    {
        $output  = '<tr><td colspan="2">'."\n";
        $output .= '<div class="submit">';
        $output .= '<input type="submit" accesskey="S" name="submitbutton" value="'.$label.' &raquo;" />';
        $output .= '</div>'."\n";
        if (FALSE != $cancelUri) {
            $output .= '<div class="cancel">';
            $output .= '<input type="submit" name="cancel" value="&laquo; Cancel" />';
            $output .= '</div>'."\n";
        }
        if (FALSE == empty($moreButtons)) {
            foreach ($moreButtons as $key => $value) {
                if (FALSE != array_key_exists('confirm_message', $value)) {
                    $message = DB::addSlashes($value['confirm_message']);
                    $onclick = ' onclick="if (confirm(\''.$message.'\') == false) { return false; }"';
                } else {
                    $onclick = '';
                }

                $output .= '<div class="button">'."\n";
                $output .= '<input type="submit"'.$onclick.' ';
                $output .= 'name="'.$value['name'].'" value="'.$value['label'].'" />'."\n";
                $output .= '<br />'.$value['description'].'</div>'."\n";
            }
        }
        $output .= '</td></tr>'."\n";
        return $output;
    }

    /**
     * Shows a button that ask for confirmation before executing the command
     *
     */
    function showConfirmButton($actionTarget, $label = 'Delete', $message)
    {
        $output = '<form action="'.$actionTarget.'" method="post">'."\n";
        $output .= '<div class="button">'."\n";
        // Convert entities to prevent Javascript errors if the message
        // contains single quotes
        // Provides: <body text='black'>
        $message = str_replace("'", '', $message);
        $output .= '<input type="submit" value="'.$label.'" onclick="if (confirm(\''.$message.'\') == true) { window.location=\''.$actionTarget.'\'; } else { return false; }" />'."\n";
        $output .= '</div></form>'."\n";
        return $output;
    }

    /**
     * Shows radio buttons
     *
     */
    /*
    function showRadioButtons($label, $name, $options_array = array()) {
        // print_r($this->errors);
        if (FALSE != $this->HasError($name)) {
            $error     = '<br />'.$this->getError($name);
            $highlight = ' class="error"';
        } else {
            $error     = '';
            $highlight = '';
        }

        $output = '<tr><td><b><span'.$highlight.'>'.$label.':</span></b></td>'."\n";
        $output .= '<td'.$highlight.'>'."\n";
        foreach ($options_array as $key => $value) {
            $output .= '<label for="'.$value['id'].'">'.$value['label'].'</label>'."\n";
            if (array_key_exists($name, $this->presetValues) AND $this->presetValues[$name] == $value['value']) {
                $output .= '<input checked="checked" type="radio" id="'.$value['id'].'" name="'.$name.'" value="'.$value['value'].'" />'."\n";
            } else {
                $output .= '<input type="radio" id="'.$value['id'].'" name="'.$name.'" value="'.$value['value'].'" />'."\n";
            }
        }
        $output .= $error.'</td></tr>'."\n";
        return $output;
    }
    */

    /**
     * Shows a list of the steps that need to be completed
     *
     */
    function showSteps($steps_array = array(), $current_step)
    {
        $output = '<table class="simple"><tr>'."\n";
        foreach ($steps_array as $key => $value) {
            $output .= '<td';
            if ($key < $current_step) {
                $output .= ' style="background-color:lightgray"> '."\n";
                $output .= '<img src="'.$this->mSettings['inc_uri'].'/icons/done.gif" width="22" height="22" alt="Done!" /> '."\n";
            } else {
                $output .= '>'."\n";
            }
            if ($key == $current_step) {
                $output .= '<b>Step '.$key.':<br /> '.$value.'</b></td>'."\n";
            } else {
                $output .= 'Step '.$key.':<br /> '.$value.'</td>'."\n";
            }
        }
        $output .= '</tr></table>'."\n";
        return $output;
    }

    /**
     * Shows a drop-down select box that highlights the current value from
     * $this->presetValues
     *
     */
    function showDropDown($label, $name, $options_array)
    {
        if (FALSE != $this->HasError($name)) {
            $error     = '<br />'.$this->getError($name);
            $highlight = ' class="error"';
        } else {
            $error     = '';
            $highlight = '';
        }
        $output  = '<tr><td>';
        $output .= '<label for="'.$name.'"'.$highlight.'>'.$label.':</label>';
        $output .= '</td>'."\n";
        $output .= '<td'.$highlight.'>';
        $output .= '<select name="'.$name.'" id="'.$name.'">'."\n";
        $output .= '<option value="">Select one...'."</option>\n";
        foreach ($options_array as $key => $value) {
            if (FALSE != array_key_exists($name, $this->presetValues) && $this->presetValues[$name] == $key) {
                $output .= '<option value="'.$key.'" selected="selected">'.$value."</option>\n";
            } else {
                $output .= '<option value="'.$key.'">'.$value."</option>\n";
            }
        }
        $output .= '</select>'.$error.'</td>'."\n";
        return $output;
    }


    /**
     * Returns an array containing all the states and D.C. and their
     * abbreviations.
     *
     */
    function getStates()
    {
        $states = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona',
                        'AR' => 'Arkansas', 'CA' => 'California',
                        'CO' => 'Colorado', 'CT' => 'Connecticut',
                        'DE' => 'Delaware', 'DC' => 'Disctrict of Columbia',
                        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii',
                        'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana',
                        'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky',
                        'LA' => 'Lousiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                        'MA' => 'Massachusetts', 'MI' => 'Michigan',
                        'MN' => 'Minnesota', 'MS' => 'Mississippi',
                        'MO' => 'Missouri', 'MT' => 'Montana',
                        'NE' => 'Nebraska', 'NV' => 'Nevada',
                        'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
                        'NM' => 'New Mexico', 'NY' => 'New York',
                        'NC' => 'North Carolina', 'ND' => 'North Dakota',
                        'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
                        'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
                        'SC' => 'South Carolina', 'SD' => 'South Dakota',
                        'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
                        'VT' => 'Vermont', 'VA' => 'Virgina',
                        'WA' => 'Washington', 'WV' => 'West Virginia',
                        'WI' => 'Wisconsin', 'WY' => 'Wyoming');
        return $states;
    }

    /**
     * Santitizes data by removing spaces and converting special html entities.
     *
     */
    function sanitizeData($data)
    {
        $data = Form::_StripSpaces($data);

        $data = Form::_StripSlashes($data);

        // Get rid of backslashes inserted by magic_quotes_gpc
        $data = Form::_ConvertHtmlSpecialChars($data);
        return $data;
    }

    /**
     * Removes leading and trailing spaces
     *
     */
     function _StripSpaces($value)
     {
        if (is_array($value)) {
            foreach ($value as $index => $val) {
                $value[$index] = Form::_StripSpaces($val);
            }
            return $value;
        } else {
            $value=trim($value);
            return $value;
        }
    }

    /**
     * Removes slashes if magic_quotes_gpc is set
     *
     */
    function _StripSlashes($value)
    {
        if (FALSE != get_magic_quotes_gpc()) {
            if (FALSE != is_array($value)) {
                foreach ($value as $index => $val) {
                    $value[$index] = Form::_StripSlashes($val);
                }
                return $value;
            } else {
                return stripslashes($value);
            }
        } else {
            return $value;
        }
    }

    /**
     * Converts special HTML characters to their HTML entities
     *
     */
    function _ConvertHtmlSpecialChars($thing)
    {
        if (FALSE != is_array($thing)) {
            $escaped = array();

            foreach ($thing as $key => $value) {
                $escaped[$key] = Form::_ConvertHtmlSpecialChars($value);
            }
            return $escaped;
        }
        return htmlspecialchars($thing, ENT_QUOTES);
    }

    /**
     * If the cancel button was pressed then send the to the URI specified
     *
     */
    function redirectOnCancel($cancelUri)
    {
        if (FALSE != array_key_exists('cancel', $_POST)) {
            header('Location: '.$cancelUri);
            // End execution of script
            exit;
        }
    }

    /**
     * Shows a formatted list of labels and values (useful for confirming info).
     *
     */
    function showList($info) {
        $output = '<table class="list">'."\n";
        foreach ($info as $key => $values) {
            if (FALSE == empty($values['value'])) {
                $output .= '<tr>';
                $output .= '<td class="label">'.$values['label'].':</td>';
                $output .= '<td>'.$values['value'].'</td>';
                $output .= '</tr>'."\n";
            }
        }
        $output .= '</table>'."\n";

        return $output;
    }

    /**
     * Shows a link that will open in a new window unless Javascript is disabled.
     *
     */
    function showNewWindowLink($title, $uri, $width = 600, $height = 500)
    {
        $output  = '<a href="'.$uri.'" onclick="';
        $output .= 'window.open(this.href, \'contents\', \'left=0, top=0, ';
        $output .= 'height='.$height.', width='.$width.', menubar=0, location=0, ';
        $output .= 'toolbar=0, scrollbars=1, status=1, resizable=1\'); ';
        $output .= 'return false;" rel="external">'.$title.'</a>';
        return $output;
    }
}
?>
