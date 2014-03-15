<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage Media
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Media;

/**
 * Manage Image
 *
 * @category   Gc
 * @package    Library
 * @subpackage Media
 */
class Image
{
    /**
     * Original image
     *
     * @var resource
     */
    protected $image;

    /**
     * Original image width
     *
     * @var integer
     */
    protected $width;

    /**
     * Original image height
     *
     * @var integer
     */
    protected $height;

    /**
     * Image resized
     *
     * @var resource
     */
    protected $imageResized;

    /**
     * Available options
     *
     * @var array
     */
    protected $availableOptions = array('auto', 'crop');

    /**
     * Initialize object
     *
     * @param string $filename filename
     *
     * @return void
     */
    public function __construct($filename = null)
    {
        if (!empty($filename)) {
            $this->open($filename);
        }
    }

    /**
     * Open image
     *
     * @param string $file File
     *
     * @return \Gc\Media\Image
     */
    public function open($file)
    {
        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
        $finfo = finfo_open($const); // return mimetype extension
        $mime  = finfo_file($finfo, $file);

        switch($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($file);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($file);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($file);
                break;
            default:
                $image = false;
                break;
        }


        $this->image = $image;
        if (empty($image)) {
            $this->width  = 0;
            $this->height = 0;
        } else {
            $this->width  = imagesx($this->image);
            $this->height = imagesy($this->image);
        }

        return $this;
    }

    /**
     * Resize image
     *
     * @param integer $newWidth        New width
     * @param integer $newHeight       New height
     * @param string  $option          Option can be (auto|crop)
     * @param string  $backgroundColor Background color
     * @param integer $sourceX         Source x
     * @param integer $sourceY         Source y
     *
     * @return \Gc\Media\Image
     */
    public function resize(
        $newWidth,
        $newHeight,
        $option = 'auto',
        $backgroundColor = '#000000',
        $sourceX = 0,
        $sourceY = 0
    ) {
        if (empty($this->image)) {
            return false;
        }

        if (!in_array($option, $this->availableOptions)) {
            $option = 'auto';
        }

        if ($option == 'crop') {
            $optimalwidth  = $newWidth;
            $optimalheight = $newHeight;
            if ($this->width < $newWidth) {
                $optimalwidth = $this->width;
            }

            if ($this->height < $newHeight) {
                $optimalheight = $this->height;
            }

            $this->crop($optimalwidth, $optimalheight, $sourceX, $sourceY);
        } else {
            $optimalheight = $this->getSizeByFixedWidth($newWidth);
            $optimalwidth  = $this->getSizeByFixedHeight($newHeight);
            if ($optimalheight > $newHeight) {
                $optimalheight = $this->getSizeByFixedWidth($optimalwidth);
            } elseif ($optimalwidth > $newWidth) {
                $optimalwidth = $this->getSizeByFixedHeight($optimalheight);
            }

            $this->imageResized = imagecreatetruecolor($optimalwidth, $optimalheight);
            imagecopyresampled(
                $this->imageResized,
                $this->image,
                0,
                0,
                0,
                0,
                $optimalwidth,
                $optimalheight,
                $this->width,
                $this->height
            );
        }

        $tmpimage = @imagecreatetruecolor($newWidth, $newHeight);

        $rgbArray = $this->hex2rgb($backgroundColor);
        if (empty($rgbArray)) {
            $backgroundColor = imagecolorallocate($tmpimage, 0, 0, 0);
        } else {
            $backgroundColor = imagecolorallocate(
                $tmpimage,
                $rgbArray['red'],
                $rgbArray['green'],
                $rgbArray['blue']
            );
        }

        imagefill($tmpimage, 0, 0, $backgroundColor);

        $dstX = (int) (0.5 * ($newWidth - $optimalwidth));
        $dstY = (int) (0.5 * ($newHeight - $optimalheight));
        imagecopyresampled(
            $tmpimage,
            $this->imageResized,
            $dstX,
            $dstY,
            0,
            0,
            $optimalwidth,
            $optimalheight,
            $optimalwidth,
            $optimalheight
        );
        $this->imageResized = $tmpimage;

        return $this;
    }

    /**
     * Convert hexa string to rbg
     *
     * @param string $hexString Hexadecimal string
     *
     * @return resource
     */
    public function hex2rgb($hexString)
    {
        $hexString = preg_replace('/[^0-9A-Fa-f]/', '', $hexString); // Gets a proper hex string
        $rgbArray  = array();
        if (strlen($hexString) == 6) {
            $colorVal          = hexdec($hexString);
            $rgbArray['red']   = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue']  = 0xFF & $colorVal;
        } elseif (strlen($hexString) == 3) {
            $rgbArray['red']   = hexdec(str_repeat(substr($hexString, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexString, 1, 1), 2));
            $rgbArray['blue']  = hexdec(str_repeat(substr($hexString, 2, 1), 2));
        } else {
            return false;
        }

        return $rgbArray;
    }

    /**
     * Get fixed height
     *
     * @param integer $newHeight New height
     *
     * @return double
     */
    protected function getSizeByFixedHeight($newHeight)
    {
        $ratio    = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;

        return floor($newWidth);
    }

    /**
     * Get fixed width
     *
     * @param integer $newWidth New width
     *
     * @return double
     */
    protected function getSizeByFixedWidth($newWidth)
    {
        $ratio     = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;

        return floor($newHeight);
    }

    /**
     * Crop image
     *
     * @param integer $newWidth  New width
     * @param integer $newHeight New height
     * @param integer $sourceX   Source x
     * @param integer $sourceY   Source y
     *
     * @return \Gc\Media\Image
     */
    protected function crop($newWidth, $newHeight, $sourceX = 0, $sourceY = 0)
    {
        $crop               = $this->imageResized;
        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $this->imageResized,
            $this->image,
            0,
            0,
            $sourceX,
            $sourceY,
            $this->width,
            $this->height,
            $this->width,
            $this->height
        );

        return $this;
    }

    /**
     * Save image
     *
     * @param string  $savePath     Save path
     * @param integer $imageQuality Image quality default is 90
     *
     * @return boolean
     */
    public function save($savePath, $imageQuality = 90)
    {
        if (empty($this->image) or empty($this->imageResized)) {
            return false;
        }

        $extension = strrchr($savePath, '.');
        $extension = strtolower($extension);
        $return    = false;
        switch($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    $return = imagejpeg($this->imageResized, $savePath, $imageQuality);
                }
                break;
            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    $return = imagegif($this->imageResized, $savePath);
                }
                break;
            case '.png':
                $scaleQuality = round(($imageQuality / 100) * 9);

                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                     $return = imagepng($this->imageResized, $savePath, $invertScaleQuality);
                }
                break;
            default:
                //Nothing to do
                break;
        }

        imagedestroy($this->imageResized);

        return $return;
    }
}
