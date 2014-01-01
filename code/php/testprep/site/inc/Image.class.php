<?php
/**
 * Uploads Images
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Image.class.php 1941 2005-05-24 22:40:58Z elijah $
 * @copyright Copyright 1999, 2002, 2003 David Fox, Dave Tufts
 * Language specific error messaging:
 *
 * Usage, setup, and license at the bottom of this page (README)
 *
 *
 *    METHODS:
 *        uploader()             - constructor, sets error message language preference
 *        max_filesize()         - set a max filesize in bytes
 *        max_image_size()     - set max pixel dimenstions for image uploads
 *        upload()             - checks if file is acceptable, uploads file to server's temp directory
 *        save_file()         - moves the uploaded file and renames it depending on the save_file($overwrite_mode)
 *
 *        get_error()         - (PRIVATE) gets language-specific error message
 *
 *    Error codes:
 *        [0] - "No file was uploaded"
 *        [1] - "Maximum file size exceeded"
 *        [2] - "Maximum image size exceeded"
 *        [3] - "Only specified file type may be uploaded"
 *        [4] - "File already exists" (save only)
 *        [5] - "Permission denied. Unable to copy file"
 *
 */
require_once 'Thumbnail.class.php';

// Max width for an image before it is resized
define ('IMAGE_MAX_WIDTH', 600);

class Image {
    private $mFile='';
    private $mPath='';
    private $mAcceptableFileTypes = 'image/gif|image/jpeg|image/pjpeg|image/png|image/bmp';
    private $mError = '';
    //    private $errors; // Depreciated (only for backward compatability)
    private $mAccepted;
    private $mMaxFileSize    = 1000000;
    private $mMaxImageWidth  = 1200;
    private $mMaxImageHeight = 1200;

    function Image($imageSettings) {
        $this->mImageSettings  = $imageSettings;
        $this->mMaxImageWidth  = (int) $imageSettings['max_width'];
        $this->mMaxImageHeight = (int) $imageSettings['max_height'];
        $this->mMaxFileSize    = (int) $imageSettings['max_file_size'];
    }

    function showResized($fileName, $imageFolder, $siteUri)
    {
        if (FALSE != is_file(dirname(__FILE__).'/../../'.$imageFolder.'/'.$fileName)) {
            $result = getimagesize(dirname(__FILE__).'/../../'.$imageFolder.'/'.$fileName);
            list($image_width, $image_height, $type, $attr) = $result;
            // echo "<img src=\"$imagePath\" $attr alt=\"getimagesize() example\" />";
            // $size = getimagesize($imageUri);
            if (FALSE == $result) {
                return '<div class="error">Unable to open image file</div>';
            }
            // $image_width = $size[0]; // Index 0
        } else {
            return '<div class="error">Unable to open image file</div>';
        }
        $image_uri = $siteUri.'/'.$imageFolder.'/'.$fileName;

        $output = '<div class="image">';
        if ($image_width > IMAGE_MAX_WIDTH) {
            $output .= '<a title="View full size image (new window)" rel="external" href="'.$image_uri.'">';
            $output .= '<img src="'.$image_uri.'" width="400"
                alt="['.$fileName.']" />';
            $output .= '</a>';
        } else {
            $output .= '<img src="'.$image_uri.'" alt="['.$fileName.']" />';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Returns the file upload error
     *
     */
    function getUploadError ()
    {
        // print_r($this->mError);
        return $this->mError[0];
    }

    /**
     * Returns TRUE or FALSE depending on whether there is a file upload error.
     *
     * @author Elijah Lofgren <elijah@truthland.com>
     */
    function hasError()
    {
        if (FALSE == is_array($this->mError)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function setError($error_text)
    {
        $this->mError[] = $error_text;
    }

    /**
     * void MaxFilesize ( int size);
     *
     * Set the maximum file size in bytes ($size), allowable by the object.
     * NOTE: PHP's configuration file also can control the maximum upload size,
     * which is set to 2 or 4 megs by default. To upload larger files, you'll
     * have to change the php.ini file first.
     *
     * @param size             (int) file size in bytes
     *
     */
    function maxFilesize($size)
    {
        $this->mMaxFilesize = (int) $size;
    }

    /**
     * void MaxImageSize ( int width, int height );
     *
     * Sets the maximum pixel dimensions. Will only be checked if the
     * uploaded file is an image
     *
     * @param width            (int) maximum pixel width of image uploads
     * @param height        (int) maximum pixel height of image uploads
     *
     */
    function maxImageSize($width, $height)
    {
        $this->mMaxImageWidth  = (int) $width;
        $this->mMaxImageHeight = (int) $height;
    }

    /**
     * Returns the final name of the uploaded image
     *
     */
    function getFinalName()
    {
        return $this->mFile['name'];
    }

    /**
     * bool Upload (string filename);
     *
     * Checks if the file is acceptable and uploads it to PHP's default
     * upload diretory
     *
     * @param filename (string) form field name of uploaded file
     *
     */
     function upload($filename = '')
     {
        if (FALSE == isset($_FILES) || FALSE == is_array($_FILES[$filename]) || FALSE == $_FILES[$filename]['name']) {
            $this->SetError('If you want to upload a file you must choose a file on your computer.');
            $this->mAccepted  = FALSE;
            $this->mFile['name'] = '';
            return FALSE;
        }

        // Copy PHP's global $_FILES array to a local array
        $this->mFile = $_FILES[$filename];
        $this->mFile['file'] = $filename;

        // Initialize empty array elements
        if (!isset($this->mFile['extention'])) $this->file['extention'] = "";
        if (!isset($this->mFile['type']))      $this->file['type']      = "";
        if (!isset($this->mFile['size']))      $this->file['size']      = "";
        if (!isset($this->mFile['width']))     $this->file['width']     = "";
        if (!isset($this->mFile['height']))    $this->file['height']    = "";
        if (!isset($this->mFile['tmp_name']))  $this->file['tmp_name']  = "";
        if (!isset($this->mFile['raw_name']))  $this->file['raw_name']  = "";

        // test max size
        if (FALSE != $this->mMaxFileSize && ($this->mFile["size"] > $this->mMaxFileSize)) {
            $this->SetError('Maximum file size exceeded. File may be no larger than ' .($this->mMaxFileSize / 1000).' KB ('.$this->mMaxFileSize.' bytes).');
            $this->mAccepted = FALSE;
            return FALSE;
        }

        if (FALSE != is_file($this->mFile["tmp_name"]) AND 0 != filesize($this->mFile["tmp_name"])) {
            $image = getimagesize($this->mFile["tmp_name"]);
            $this->mFile["width"]  = $image[0];
            $this->mFile["height"] = $image[1];

            // Image Type is returned from getimagesize() function
            switch($image[2]) {
            case 1:
                $this->mFile["extention"] = ".gif";
                break;

            case 2:
                $this->mFile["extention"] = ".jpg";
                break;

            case 3:
                $this->mFile["extention"] = ".png";
                break;

            case 4:
                $this->mFile["extention"] = ".swf";
                break;

            case 5:
                $this->mFile["extention"] = ".psd";
                break;

            case 6:
                $this->mFile["extention"] = ".bmp";
                break;

            case 7:
                $this->mFile["extention"] = ".tif";
                break;

            case 8:
                $this->mFile["extention"] = ".tif";
                break;

            default:
                $this->mFile["extention"] = '.txt';
                break;
            }
            // check to see if the file is of type specified
            if (FALSE != $this->mAcceptableFileTypes) {
            if (FALSE != trim($this->mFile['type']) && stristr($this->mAcceptableFileTypes, $this->mFile['type'])) {
                $this->mAccepted = TRUE;
            } else {
                $this->mAccepted = FALSE;
                $file_types = str_replace('|', ' or ', $this->mAcceptableFileTypes);
                $file_types = str_replace('image/', '', $file_types);
                $this->SetError('Only '.$file_types.' files may be uploaded.');
            }
        } else {
            $this->mAccepted = TRUE;
        }

        } else {
            $this->setError('Invalid image file.');
            $this->mAccepted = FALSE;
        }
        return (bool) $this->mAccepted;
    }

    /**
    * bool SaveFile ( string path );
     *
     * Cleans up the filename, copies the file from PHP's temp location to $path,
     * and checks the overwrite_mode
     *
     * @param path                (string) File path to your upload directory
     */
    function saveFile($path)
    {
        $this->mPath = $path;
        //    $overwrite_mode = "1";
        if (FALSE != $this->HasError()) {
            return FALSE;
        }
        if (FALSE != strlen($this->mPath) > 0) {
            if ('/' != $this->mPath[strlen($this->mPath)-1]) {
                $this->mPath = $this->mPath.'/';
            }
        }
        //    $copy       = "";
        //    $n          = 1;
        $success = FALSE;

        if (FALSE != $this->mAccepted) {
            // Clean up file name (only lowercase letters, numbers and underscores)
            $this->mFile['name'] = ereg_replace("[^a-z0-9._]", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($this->mFile['name']))));

            // Get the raw name of the file (without its extenstion)
            if (FALSE != ereg("(\.)([a-z0-9]{2,5})$", $this->mFile['name'])) {
                $pos = strrpos($this->mFile['name'], '.');
                if (FALSE == $this->mFile['extention']) {
                    $this->mFile['extention'] = substr($this->mFile['name'], $pos, strlen($this->mFile['name']));
                }
                $this->mFile['raw_name'] = substr($this->mFile['name'], 0, $pos);
            } else {
                $this->mFile['raw_name'] = $this->mFile['name'];
                if ($this->mFile['extention']) {
                    $this->mFile['name'] = $this->mFile['name'] . $this->mFile['extention'];
                }
            }

            // Make sure the upload tarGet folder exists
            if (FALSE == file_exists($this->mPath)) {
                $success = FALSE;
                $this->SetError('Folder '.$this->mPath.' does not exist.');
            }

            if (FALSE == $this->HasError()) {
                // overwrite the old file
                if (FALSE == copy($this->mFile['tmp_name'], $this->mPath . $this->mFile['name'])) {
                    $success = FALSE;
                    $this->SetError('Permission denied. Unable to copy file to "' . $this->mPath . '"');
                } else {
                    $success = TRUE;
                }
            }

            if (FALSE == $success) {
                unset($this->mFile['tmp_name']);
            } else {
                            // test max image size
            // if (($this->mMaxImageWidth || $this->mMaxImageHeight) && (($this->mFile["width"] > $this->mMaxImageWidth) || ($this->mFile["height"] > $this->mMaxImageHeight))) {
                if (($this->mMaxImageWidth || $this->mMaxImageHeight) && (($this->mFile["width"] > $this->mMaxImageWidth) || ($this->mFile["height"] > $this->mMaxImageHeight))) {
                // $this->SetError('Maximum image size exceeded. Image may be no more than ' . $this->mMaxImageWidth . ' x ' . $this->mMaxImageHeight . ' pixels.');
                // $this->mAccepted = FALSE;
                // return FALSE;
                if (FALSE == $this->resizeImage()) {
                    $this->SetError($this->mThumbnailError);
                    $success = FALSE;
                }
            }


            }
            return (bool) $success;
        } else {
            $this->SetError('Only ' . str_replace('|', ' or ', $this->mAcceptable_file_types) . ' files may be uploaded.');
            return FALSE;
        }
    }

    /**
    * Resizes images if they are very large. This is done to save server disk
    * space and bandwidth.
    *
    */
    function resizeImage()
    {
        // Resize image using the Thumbnail class
        // Create Thunbnail
            // echo $this->mPath;
            // echo '<br />'.$this->mFile['name'];
            $Thumbnail = new Thumbnail($this->mPath, $this->mFile['name'], $this->mImageSettings);

            $Thumbnail->SetHeight($this->mMaxImageHeight);
            $Thumbnail->SetWidth($this->mMaxImageWidth);

            $thumbnail_name = $Thumbnail->Save($this->mPath, $this->mFile['name']);
            // echo '<br />'.$thumbnail_name;
            if (FALSE != $Thumbnail->HasError()) {
                // echo 'Thumbnail errors';
                $thumbnail_success = FALSE;
                $this->mThumbnailError  = '<div class="error">';
                $this->mThumbnailError .= $Thumbnail->getError();
                $this->mThumbnailError .= '</div>';
            } else {
                $thumbnail_success = TRUE;
                // echo 'No Thumbnail errors';
                $this->mThumbnailError = TRUE;
            }
            return $thumbnail_success;
    }

    /**
     * string _getError(int error_code);
     *
     * Gets the correct error message for language set by constructor
     *
     * @param error_code        (int) error code
     *
     */
    /*
    function _getError($error_code='') {
        $error_message = array();
        $error_code    = (int) $error_code;

        $error_message[0] = 'You did not choose a file to upload.';
        $error_message[1] = 'Maximum file size exceeded. File may be no larger than ' . $this->max_filesize/1000 . ' KB (' . $this->max_filesize . ' bytes).';
        $error_message[2] = 'Maximum image size exceeded. Image may be no more than ' . $this->max_image_width . ' x ' . $this->max_image_height . ' pixels.';
        $error_message[3] = 'Only ' . str_replace('|', ' or ', $this->acceptable_file_types) . ' files may be uploaded.';
        $error_message[4] = 'File "' . $this->path . $this->file["name"] . '" already exists.';
        $error_message[5] = 'Permission denied. Unable to copy file to "' . $this->path . '"';

        // for backward compatability:
    //    $this->errors[$error_code] = $error_message[$error_code];

        return $error_message[$error_code];
    }
    */

    /**
     * void CleanupTextFile (string file);
     *
     * Convert Mac and/or PC line breaks to UNIX by opening
     * and rewriting the file on the server
     *
     * @param file            (string) Path and name of text file
     *
     */
    /*
    function CleanupTextFile($file){
        // chr(13)  = CR (carridge return) = Macintosh
        // chr(10)  = LF (line feed)       = Unix
        // Win line break = CRLF
        $new_file  = '';
        $old_file  = '';
        $fcontents = file($file);
        while (list ($line_num, $line) = each($fcontents)) {
            $old_file .= $line;
            $new_file .= str_replace(chr(13), chr(10), $line);
        }
        if ($old_file != $new_file) {
            // Open the uploaded file, and re-write it with the new changes
            $fp = fopen($file, "w");
            fwrite($fp, $new_file);
            fclose($fp);
        }
    }
    */

}


/*
<readme>

    fileupload-class.php can be used to upload files of any type
    to a web server using a web browser. The uploaded file's name will
    Get cleaned up - special characters will be deleted, and spaces
    Get replaced with underscores, and moved to a specified
    directory (on your server). fileupload-class.php also does its best to
    determine the file's type (text, GIF, JPEG, etc). If the user
    has named the file with the correct extension (.txt, .gif, etc),
    then the class will use that, but if the user tries to upload
    an extensionless file, PHP does can identify text, gif, jpeg,
    and png files for you. As a last resort, if there is no
    specified extension, and PHP can not determine the type, you
    can set a default extension to be added.

    SETUP:
        Make sure that the directory that you plan on uploading
        files to has enough permissions for your web server to
        write/upload to it. (usually, this means making it world writable)
            - cd /your/web/dir
            - chmod 777 <fileupload_dir>

        The HTML FORM used to upload the file should look like this:
        <form method="post" action="upload.php" enctype="multipart/form-data">
            <input type="file" name="userfile">
            <input type="submit" value="Submit">
        </form>


    USAGE:
        // Create a new instance of the class
        $my_uploader = new uploader;

        // OPTIONAL: set the max filesize of uploadable files in bytes
        $my_uploader->max_filesize(90000);

        // OPTIONAL: if you're uploading images, you can set the max pixel dimensions
        $my_uploader->max_image_size(150, 300); // max_image_size($width, $height)

        // UPLOAD the file
        $my_uploader->upload("userfile", "", ".jpg");

        // MOVE THE FILE to its final destination
        //    $mode = 1 ::    overwrite existing file
        //    $mode = 2 ::    rename new file if a file
        //                       with the same name already
        //                     exists: file.txt becomes file_copy0.txt
        //    $mode = 3 ::    do nothing if a file with the
        //                       same name already exists
        $my_uploader->save_file("/your/web/dir/fileupload_dir", int $mode);

        // Check if everything worked
        if ($my_uploader->error) {
            echo $my_uploader->error . "<br>";

        } else {
            // Successful upload!
            $file_name = $my_uploader->file['name'];
            print($file_name . " was successfully uploaded!");

        }

</readme>


<license>

    ///// fileupload-class.php /////
    Copyright (c) 1999, 2002, 2003 David Fox, Angryrobot Productions
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions
    are met:
    1. Redistributions of source code must retain the above copyright
       notice, this list of conditions and the following disclaimer.
    2. Redistributions in binary form must reproduce the above
       copyright notice, this list of conditions and the following
       disclaimer in the documentation and/or other materials provided
       with the distribution.
    3. Neither the name of author nor the names of its contributors
       may be used to endorse or promote products derived from this
       software without specific prior written permission.

    DISCLAIMER:
    THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
    TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
    PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR
    CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
    SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
    LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF
    USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
    AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
    LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
    IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
    THE POSSIBILITY OF SUCH DAMAGE.

</license>
*/
?>
