<?php

require 'config.transImage.php';

/**
*  class transImage
*  transform the image and watermarking
*/
class transImage {
    
    const ROTATEONEXIF = CONFIG_transImage::ROTATEONEXIF;
    const IMGTYPES     = CONFIG_transImage::IMGTYPES;
    
    /**
     *  predefined types of images
     *  for use in self::out() and self::save()
     */
    const IMAGE_TYPE_JPEG = 'jpeg';
    const IMAGE_TYPE_GIF  = 'gif';
    const IMAGE_TYPE_PNG  = 'png';
    
    private $ImgRes;
    private $orient = 1; // default 1 - normal orientation
    private $width;
    private $height;
    private $maxsx = CONFIG_transImage::MAXSX;
    private $maxsy = CONFIG_transImage::MAXSY;
    
    /**
     *  flag for manual close object
     */
    private $closed = false;
    
    /**
     *  private constructur.
     *  For create object, use transImage::createFromFile()
     */
    private function __construct($ImgRes, $width, $height, $Orient = 1) {
        $this->ImgRes = $ImgRes;
        $this->orient = (self::ROTATEONEXIF) ? $Orient : 1;
        $this->width  = $width;
        $this->height = $height;
        $this->ImgRes = $ImgRes;
    }
    
    public function __destruct(){
        return $this->close();
    }
    
    /**
     *  function close()
     *  for manual close object 
     */    
    public function close(){
        if($this->closed)
            return false;
        $this->closed = true;
        imageDestroy($this->ImgRes);
        return true;
    }
    
    /*
     * function createFromFile
     * @param string $fileName
     * @param string $alarm
     * @return self object or false on failure
     */
    public function createFromFile($fileName, &$alarm){
        if(!file_exists($fileName)) {
            $alarm = 'file not exist';
            return false;
        }
        if( !($imgProp = getimagesize($fileName)) ) {
            $alarm = 'is not image';
            return false;
        }
        
        list($width, $height, $imgNtype) = $imgProp;
        $imgtypesarray = Array(
                        IMAGETYPE_GIF  => 'gif',
                        IMAGETYPE_JPEG => 'jpeg',
                        IMAGETYPE_PNG  => 'png',
                        IMAGETYPE_WBMP => 'wbmp',
                        IMAGETYPE_XBM  => 'xbm'                        
                       );
        if( !isset($imgtypesarray[$imgNtype]) ||
            !in_array($imgNtype, explode(',', self::IMGTYPES)) ) {
            $alarm = 'image type not allowed';
            return false;
        }
        
        if( !($ImgRes = call_user_func('imageCreateFrom' . $imgtypesarray[$imgNtype], $fileName)) ) {
            $alarm = 'create image error';
            return false;
        }
        
        $Orient = 1;
        if(IMAGETYPE_JPEG == $imgNtype && self::ROTATEONEXIF &&
        ($exif = exif_read_data($fileName)) && isset($exif['Orientation']) ) {
            $Orient = $exif['Orientation'];
        }
        
        $selfObj = new self($ImgRes, $width, $height, $Orient);
        
        # Fill a white background, if the format requires transparency
        if( !$selfObj->normalSize() &&
        (IMAGETYPE_GIF==$imgNtype || IMAGETYPE_PNG==$imgNtype) ) {
            $newImage = $selfObj->getFill();
            imagecopy($newImage, $selfObj->ImgRes, 0,0,0,0, $selfObj->width, $selfObj->height);
            imageDestroy($selfObj->ImgRes);
            $selfObj->ImgRes = $newImage;
        }
        
        if(1!=$selfObj->orient) {
            $selfObj->rotateExif();
        }
        return $selfObj;
    }
    
    /*
     * function createFromThumb
     * create image from Exif thumbnail for fast preview
     * @param string $fileName
     * @param string $alarm
     * @param int $max size Ox
     * @param int $max size Oy
     * @return self object or false on failure
     */
    public function createFromThumb($fileName, &$alarm, $maxsx = 0, $maxsy = 0){
        if(!file_exists($fileName)) {
            $alarm = 'file not exist';
            return false;
        }
        if( !($imgProp = getimagesize($fileName)) ) {
            $alarm = 'is not image';
            return false;
        }
        if( IMAGETYPE_JPEG !== $imgProp[2] ||
        !($imgStr = exif_thumbnail ($fileName, $width, $height, $imageNtype)) ||
        IMAGETYPE_JPEG !== $imageNtype ||
        !($ImgRes = imageCreateFromString($imgStr)) ) {
            $alarm = 'thumbnail not supported';
            return false;
        }
        
        $Orient = 1;
        if(self::ROTATEONEXIF && ($exif = exif_read_data($fileName)) &&
        isset($exif['Orientation']) ) {
            $Orient = $exif['Orientation'];
        }
        $selfObj = new self($ImgRes, $width, $height, $Orient);
        !$maxsx || ($selfObj->maxsx = $maxsx);
        !$maxsy || ($selfObj->maxsy = $maxsy);
        
        $selfObj->normalSize();
        
        if(1!=$selfObj->orient) {
            $selfObj->rotateExif();
        }
        return $selfObj;
    }
    
    /*
     * function resize
     * @param int $maxW max width
     * @param int $maxH msx height
     * @param bool $getCopy if false - resize self, else return resized copy
     * @return bool or self object, depending on the value of $getCopy 
     */
    public function resize($maxW, $maxH, $getCopy = false) {
        $newW = $width = $this->width; $newH = $height = $this->height;
        if($newW < $maxW && $newH < $maxH) {
            if(!$getCopy) {
                return false;
            }
            $newImage = $this->getFill();
            imagecopy($newImage, $this->ImgRes,0,0,0,0,$newW,$newH);            
            return new self($newImage, $newW, $newH);
        }
        if($newW > $maxW) {
            $newH = round($newH*$maxW/$newW);
            $newW = $maxW;
        }
        if($newH > $maxH) {
            $newW = round($newW*$maxH/$newH);
            $newH = $maxH;
        }
        
        $newImage = $this->getFill($newW, $newH);
        if(!imagecopyresampled($newImage,$this->ImgRes,0,0,0,0,$newW,$newH,$width,$height)) {
            imageDestroy($newImage);
            return false;
        }
        
        if( $getCopy ) {
            return new self($newImage, $newW, $newH);
        }
        imageDestroy($this->ImgRes);
        $this->width  = $newW;
        $this->height = $newH;
        $this->ImgRes = $newImage;
        return true;
    }
    
    /*
     * private function normalSize
     * Normalization of the image size.
     * Performed before the turn and using
     * @param $imgRes gdresourse
     * @param void
     * @return bool. True if resized
     */
    private function normalSize() {
        # effective sizes
        $effW = $width = $this->width; $effH = $height = $this->height;
        # max sizes
        $maxW = $this->maxsx; $maxH = $this->maxsy;
        
        if(6==$this->orient || 8==$this->orient || 5==$this->orient || 7==$this->orient) {
            $effH = $width; $effW = $height;
            $maxH = $this->maxsx; $maxW = $this->maxsy;
        }
        if($effW <= $this->maxsx && $effH <= $this->maxsy) {
            return false;
        }
        
        return $this->resize($maxW, $maxH);
    }    
    
    /*
     * private function rotateExif
     * rotate the image according to exif
     * @param $void
     * @return bool. True if successfully
     */
    private function rotateExif() {
        $degArr = Array(
                3 => 180,
                6 => 270,
                8 => 90,
                4 => 180,
                7 => 270,
                5 => 90,
                2 => 0
                );
        if(!isset($degArr[$this->orient])) {
            return true;
        }
        $orient = $this->orient;
        if(6==$orient || 8==$orient || 5==$orient || 7==$orient) {
            $sw           = $this->width;
            $this->width  = $this->height;
            $this->height = $sw;
        }
        
        if( !function_exists("imagerotate") ) {
            $width  = $this->width;
            $height = $this->height;
            #image rotate and reflections oX
            $rotRes = imagecreatetruecolor($width,$height);
            $srcImg = $this->ImgRes;
            for($x=0; $x<$width; $x++) {
                for($y=0; $y<$height; $y++) {
                    if(3==$orient) {
                        $srcX = $width-$x-1;
                        $srcY = $height-$y-1;
                    } elseif(8==$orient) {
                        $srcX = $height-$y-1;
                        $srcY = $x;
                    } elseif(6==$orient) {
                        $srcX = $y;
                        $srcY = $width-$x-1;;
                    } elseif(4==$orient) {
                        $srcX = $x;
                        $srcY = $height-$y-1;
                    } elseif(7==$orient) {
                        $srcX = $height-$y-1;
                        $srcY = $width-$x-1;
                    } elseif(5==$orient) {
                        $srcX = $y;
                        $srcY = $x;;
                    }  elseif(2==$orient) {
                        $srcX = $width-$x-1;
                        $srcY = $y;
                    } 
                    $color = imagecolorat($srcImg, $srcX, $srcY);
                    imagesetpixel($rotRes, $x, $y, $color);
                }
            }
        } else {
            $rotRes = imagerotate($this->ImgRes, $degArr[$this->orient], 0);
            if(2==$orient || 4==$orient || 5==$orient || 7==$orient) {
                #image reflections oX
                $width  = $this->width;
                $height = $this->height;        
                $newImg = imagecreatetruecolor($width, $height);
                for ($x = 0; $x < $width; $x++) {
                    imagecopy($newImg, $rotRes, $x+1, 0, $width-$x+1, 0, 1, $height);
                }
                imageDestroy($rotRes);
                $rotRes = $newImg;     
            }
        }
        
        imageDestroy($this->ImgRes);
        $this->ImgRes = $rotRes;
        return true;
    }
    
    /*
     * function getFill
     * create and return filled img area
     * @param int $width
     * @param int $height
     * @return image resource identifier
     */
    private function getFill($width = NULL, $height = NULL) {
        $width  = ($width)  ? $width : $this->width;
        $height = ($height) ? $height : $this->height;
        $newImage = ImageCreateTrueColor($width, $height);
        imagefill($newImage, 0, 0, imagecolorallocate($newImage, 255, 255, 255));
        return $newImage;
    }
    
    public function getWidth() {
        return $this->width;
    }
    
    public function getHeight() {
        return $this->height;
    }
    
    /*
     * function getString
     * can be used for use in sending data in json format 
     * @param string image type
     * @return string binary representation of the image, encoded at base64
     */
    public function getString($type = self::IMAGE_TYPE_JPEG) {
        ob_start();
        call_user_func('image' . $type, $this->ImgRes);
        $imguot = ob_get_contents();
        ob_end_clean();
        return base64_encode($imguot);
    }
    
    public function out($type = self::IMAGE_TYPE_JPEG) {
        header('Content-Type: image/' . $type);
        $quality = 75;
        call_user_func('image' . $type, $this->ImgRes, NULL, $quality);
    }
    
    /*
     * function save
     * @param string $toFile
     * @param string $type image string-type
     */
    public function save($toFile, $type = self::IMAGE_TYPE_JPEG) {
        return call_user_func('image' . $type, $this->ImgRes, $toFile);
    }    
    
    /*
     * function addWatermark
     * @param waterMark $watermark. waterMark implemented object
     */
    public function addWatermark(waterMark $watermark) {
        $watermark->set($this->ImgRes, $this->width, $this->height);
    }
    
 }
 
 
 /**
 *  interface waterMark
 *  waterMark for using in class transform
 */
 interface waterMark {
    /*
     * function set
     * set self watermark on image $img 
     * @param $img - image resource identifier
     * @param int $width 
     * @param int $height
     * @return void
     */     
    function set($img, $width = 0, $height = 0);
 }
