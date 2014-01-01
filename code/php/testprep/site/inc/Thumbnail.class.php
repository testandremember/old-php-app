<?php
/**
 * Creates image thumbnails
 *
 * The Shiege Iseng Resize Class was created on 11 March 2003.
 * More info at: http://kentung.f2o.org/scripts/thumbnail/
 * Thanks to: Dian Suryandari <dianhau@yahoo.com>
 * @author Shiege Iseng <shiegege@yahoo.com>
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Thumbnail.class.php 1941 2005-05-24 22:40:58Z elijah $
 * @package NiftyCMS
 * Sample :
 * <code>
 * $thumb=new thumbnail("./shiegege.jpg");			// generate image_file, set filename to resize
 * $thumb->size_width(100);				// set width for thumbnail, or
 * $thumb->size_height(300);				// set height for thumbnail, or
 * $thumb->size_auto(200);					// set the biggest width or height for thumbnail
 * $thumb->jpeg_quality(75);				// [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
 * $thumb->show();						// show your thumbnail
 * $thumb->save("./huhu.jpg");				// save your thumbnail to file
 * </code>
 * Note :
 * - GD must Enabled
 * - Autodetect file extension (.jpg/jpeg, .png, .gif, .wbmp)
 *   but some server can't generate .gif / .wbmp file types
 * - If your GD not support 'ImageCreateTrueColor' function,
 *   change one line from 'ImageCreateTrueColor' to 'ImageCreate'
 *   (the position in 'show' and 'save' function)
 */

class Thumbnail
{
	private $img;
    // Holds error message, if any
    private $mError = '';

	function Thumbnail($path, $filename, $imageSettings)
	{
        if (FALSE != is_file($path.$filename)) {
            $this->img['filename'] = $filename;

            //detect image format
            $this->img["format"] = ereg_replace(".*\.(.*)$","\\1",$path.$filename);
            $this->img["format"] = strtoupper($this->img["format"]);
            if ('JPG' == $this->img["format"] || 'JPEG' == $this->img["format"]) {
                //JPEG
                $this->img["format"] = 'JPEG';
                $this->img["src"] = ImageCreateFromJPEG($path.$filename);
            } elseif ('PNG' == $this->img["format"]) {
                //PNG
                $this->img["format"] = 'PNG';
                $this->img["src"] = ImageCreateFromPNG($path.$filename);
            } elseif ('GIF' == $this->img["format"]) {
                //GIF
                $this->img["format"] = 'GIF';
                $this->img["src"] = ImageCreateFromGIF($path.$filename);
            } elseif ('BMP' == $this->img["format"]) {
                //BMP
                $this->img["format"] = 'BMP';
                $this->img["src"] = $this->ImageCreateFromBMP($path.$filename);
            } elseif ('WBMP' == $this->img["format"]) {
                //WBMP
                $this->img["format"] = 'WBMP';
                $this->img["src"] = ImageCreateFromWBMP($path.$filename);
            } else {
                //DEFAULT
                $this->mError = "Not Supported File";
                return FALSE;
            }
            if (FALSE == $this->img['src']) {
                $this->mError = 'Thumbnail creation failed. Invalid image file';
                return FALSE;
            }
            $this->img["width"] = imagesx($this->img["src"]);
            $this->img["height"] = imagesy($this->img["src"]);
            // echo 'width: '.$this->img["width"];
            // echo 'tiggi: '.$this->img["height"];
            //default quality jpeg
            // $this->img["quality"] = 75;
            // Set image quality
            $this->img["quality"] = $imageSettings['image_quality'];
            // Set thumbnail width and height

            // $Thumbnail->SetHeight($imageSettings['thumbnail_height']);
            // $Thumbnail->SetWidth($image_settings['thumbnail_width']);
            $this->img["height_thumb"] = $imageSettings['thumbnail_height'];
            $this->img["width_thumb"] = ($this->img["height_thumb"]/$this->img["height"])*$this->img["width"];
            $this->img["width_thumb"] = $imageSettings['thumbnail_width'];
            $this->img["height_thumb"] = ($this->img["width_thumb"]/$this->img["width"])*$this->img["height"];
            $this->mError = '';
           // return TRUE;
        } else {
            $this->mError = 'Invalid Source Image.';
          //  return FALSE;
        }
    }

    function ConvertBMP2GD($src, $dest = false) {
        if(!($src_f = fopen($src, "rb"))) {
            return false;
        }
        if(!($dest_f = fopen($dest, "wb"))) {
            return false;
        }
        $header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f,
                                                                 14));
        $info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",
                       fread($src_f, 40));

        extract($info);
        extract($header);

        if($type != 0x4D42) { // signature "BM"
            return false;
        }

        $palette_size = $offset - 54;
        $ncolor = $palette_size / 4;
        $gd_header = "";
        // true-color vs. palette
        $gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
        $gd_header .= pack("n2", $width, $height);
        $gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
        if($palette_size) {
            $gd_header .= pack("n", $ncolor);
        }
        // no transparency
        $gd_header .= "\xFF\xFF\xFF\xFF";

        fwrite($dest_f, $gd_header);

        if($palette_size) {
            $palette = fread($src_f, $palette_size);
            $gd_palette = "";
            $j = 0;
            while($j < $palette_size) {
                $b = $palette{$j++};
                $g = $palette{$j++};
                $r = $palette{$j++};
                $a = $palette{$j++};
                $gd_palette .= "$r$g$b$a";
            }
            $gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
            fwrite($dest_f, $gd_palette);
        }

        $scan_line_size = (($bits * $width) + 7) >> 3;
        $scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size &
                                                           0x03) : 0;

        for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
            // BMP stores scan lines starting from bottom
            fseek($src_f, $offset + (($scan_line_size + $scan_line_align) *
                                     $l));
            $scan_line = fread($src_f, $scan_line_size);
            if($bits == 24) {
                $gd_scan_line = "";
                $j = 0;
                while($j < $scan_line_size) {
                    $b = $scan_line{$j++};
                    $g = $scan_line{$j++};
                    $r = $scan_line{$j++};
                    $gd_scan_line .= "\x00$r$g$b";
                }
            }
            else if($bits == 8) {
                $gd_scan_line = $scan_line;
            }
            else if($bits == 4) {
                $gd_scan_line = "";
                $j = 0;
                while($j < $scan_line_size) {
                    $byte = ord($scan_line{$j++});
                    $p1 = chr($byte >> 4);
                    $p2 = chr($byte & 0x0F);
                    $gd_scan_line .= "$p1$p2";
                }
                $gd_scan_line = substr($gd_scan_line, 0, $width);
            }
            else if($bits == 1) {
                $gd_scan_line = "";
                $j = 0;
                while($j < $scan_line_size) {
                    $byte = ord($scan_line{$j++});
                    $p1 = chr((int) (($byte & 0x80) != 0));
                    $p2 = chr((int) (($byte & 0x40) != 0));
                    $p3 = chr((int) (($byte & 0x20) != 0));
                    $p4 = chr((int) (($byte & 0x10) != 0));
                    $p5 = chr((int) (($byte & 0x08) != 0));
                    $p6 = chr((int) (($byte & 0x04) != 0));
                    $p7 = chr((int) (($byte & 0x02) != 0));
                    $p8 = chr((int) (($byte & 0x01) != 0));
                    $gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
                }
                $gd_scan_line = substr($gd_scan_line, 0, $width);
            }

            fwrite($dest_f, $gd_scan_line);
        }
        fclose($src_f);
        fclose($dest_f);
        return true;
    }

    function imagecreatefrombmp($filename) {
        $tmp_name = tempnam("/tmp", "GD");
        if($this->ConvertBMP2GD($filename, $tmp_name)) {
            $img = imagecreatefromgd($tmp_name);
            unlink($tmp_name);
            return $img;
        }
        return false;
    }

    /**
     * @deprecated 2004-08-31
     */
	function setHeight($size=100)
	{
        if (FALSE == $this->hasError()) {
            //height
            $this->img["height_thumb"] = $size;
            $this->img["width_thumb"] = ($this->img["height_thumb"]/$this->img["height"])*$this->img["width"];
        }
	}

    /**
     * @deprecated 2004-08-31
     */
	function setWidth($size=100)
	{
		if (FALSE == $this->hasError()) {
            //width
            $this->img["width_thumb"] = $size;
            $this->img["height_thumb"] = ($this->img["width_thumb"]/$this->img["width"])*$this->img["height"];
        }
	}

    /*
	function size_auto($size=100)
	{
		//size
		if ($this->img["width"]>=$this->img["height"]) {
    		$this->img["width_thumb"]=$size;
    		$this->img["height_thumb"] = ($this->img["width_thumb"]/$this->img["width"])*$this->img["height"];
		} else {
	    	$this->img["height_thumb"]=$size;
    		$this->img["width_thumb"] = ($this->img["height_thumb"]/$this->img["height"])*$this->img["width"];
 		}
	}
    */
	/*
    function jpeg_quality($quality=75)
	{
		//jpeg quality
		$this->img["quality"]=$quality;
	}
    */
	/*
    function show()
	{
		//show thumb
		header("Content-Type: image/".$this->img["format"]);

		// change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function
		$this->img["des"] = ImageCreateTrueColor($this->img["width_thumb"],$this->img["height_thumb"]);
    		imagecopyresized ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["width_thumb"], $this->img["height_thumb"], $this->img["width"], $this->img["height"]);

		if ($this->img["format"]=="JPG" || $this->img["format"]=="JPEG") {
			//JPEG
			imageJPEG($this->img["des"],"",$this->img["quality"]);
		} elseif ($this->img["format"]=="PNG") {
			//PNG
			imagePNG($this->img["des"]);
		} elseif ($this->img["format"]=="GIF") {
			//GIF
			imageGIF($this->img["des"]);
		} elseif ($this->img["format"]=="WBMP") {
			//WBMP
			imageWBMP($this->img["des"]);
		}
	}
*/

    /**
    * Get which version of GD is installed, if any.
    *
    * Returns the version (1 or 2) of the GD extension.
    */
    function gdVersion()
    {
       if (FALSE == extension_loaded('gd')) {
           return FALSE;
       }
       // Start output buffering
       ob_start();
       phpinfo(8);
       $info = ob_get_contents();
       ob_end_clean();
       $info = stristr($info, 'gd version');
       preg_match('/\d/', $info, $gd);
       // Return the GD version
       return $gd[0];
    } // end function gdVersion()

    /**
     * Creates and saves the image thumbnail.
     *
     */
    function save($path = '', $filename = '')
    {
        if (FALSE != $this->hasError()) {
            return FALSE;
        }
        //save thumb
        if (FALSE != empty($filename)) {
            $filename = strtolower("./thumb.".$this->img["format"]);
        }
		/* change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function*/
        //	$this->img["des"] = ImageCreateTrueColor($this->img["width_thumb"],$this->img["height_thumb"]);

        // $this->img["des"] = ImageCreate($this->img["width_thumb"],$this->img["height_thumb"]);

        if ($this->img['width'] <= $this->img['width_thumb'] && $this->img['height'] <= $this->img['height_thumb'] && 'GIF' != $this->img["format"]) {
        //    echo $this->img['width'].'img width<br />';
          //  echo $this->img['height'].'img height<br />';
            // echo 'Thumbnail will be too big';
            $filename = $this->img['filename'];
            // echo $filename;
        } else {

            // The function is easy to use.
            $gdv = $this->gdVersion();
            if (FALSE != $gdv) {
                if ($gdv >= 2) {
                    //   echo 'imageCreateTruecolor() and imageCopyResampled() functions may be used.';
                    if ($this->img['width'] <= $this->img['width_thumb'] && $this->img['height'] <= $this->img['height_thumb']) {
                        // echo $this->img['width'].'img width<br />';
                        //echo $this->img['height'].'img height<br />';
                        $this->img["des"] = ImageCreateTrueColor($this->img["width"], $this->img["height"]);
                    } else {
                        $this->img["des"] = ImageCreateTrueColor($this->img["width_thumb"], $this->img["height_thumb"]);
                    }
                } else {
                    //   echo 'imageCreate() and imageCopyResized() functions must be used.';
                    $this->img["des"] = ImageCreate($this->img["width_thumb"], $this->img["height_thumb"]);
                    // $thumb = imagecreate ($this->img["width_thumb"],$this->img["height_thumb"]);
                    // imageJPEG($thumb,"images/temp.jpg");
                    // $thumb = imagecreatefromjpeg($);
                }
            } else {
                echo "The GD extension isn't loaded.";
            }
            // This makes sure that static thumbnails are generated for animated GIFs
            // if ($this->img['width'] <= $this->img['width_thumb'] && $this->img['height'] <= $this->img['height_thumb']) {
               // imagecopyresized ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["width"], $this->img["height"], $this->img["width"], $this->img["height"]);
           // } else {
                imagecopyresized ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["width_thumb"], $this->img["height_thumb"], $this->img["width"], $this->img["height"]);
            // }

            // $thumb = imagecreate ($width, $height);
            // imageJPEG($thumb,"images/temp.jpg");
            // $thumb = imagecreatefromjpeg("images/temp.jpg");

            //imagecopyresized ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["width_thumb"], $this->img["height_thumb"], $this->img["width"], $this->img["height"]);

            if ('JPG' == $this->img["format"] || 'JPEG' == $this->img["format"]) {
                //JPEG
                // echo $this->img["quality"];
                imageJPEG($this->img["des"], $path.$filename, $this->img["quality"]);
            } elseif ('PNG' == $this->img["format"]) {
                //PNG
                imagePNG($this->img["des"], $path.$filename);
            } elseif ('BMP' == $this->img["format"]) {
                //BMP
                $filename = str_replace('.bmp', '.jpg', $filename);
                imageJPEG($this->img["des"], $path.$filename);
            } elseif ('GIF' == $this->img["format"]) {
                //GIF
                if (FALSE != function_exists('imageGIF')) {
                    imageGIF($this->img["des"], $path.$filename);
                } else {
                    // $this->mError = 'Error: Unable to generate thumbnail. Server does not have ability to generate GIF images.';
                    // imagecopy($this->img["des"], $path.$filename);
                    $filename = str_replace('.gif', '.png', $filename);
                    imagePNG($this->img["des"], $path.$filename);
                    // echo $this->mError;
                }
            } elseif ('WBMP' == $this->img["format"]) {
                //WBMP
                imageWBMP($this->img["des"], $path.$filename);
            }
            imagedestroy($this->img['des']);
        }
        return $filename;
    }

    /**
     * Returns TRUE or FALSE depending on whether there were Thumbnail
     * generation errors.
     *
     */
    function hasError()
    {
        // echo $this->mError;
        if ($this->mError != '') {
            // There were errors
            return TRUE;
        } else {
            // There were not errors
            return FALSE;
        }
    }

    /**
     * Returns the Thumbnail generation error
     *
     */
    function getError()
    {
        // echo $this->mError;
        if (FALSE != isset($this->mError) AND $this->mError != '') {
            // There were errors
            return $this->mError;
        } else {
            // There were not errors
            return 'There was no thumbnail error.';
        }
    }
}
?>
