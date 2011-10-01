<?php

 require 'config.transImage.php';
 
 /**
 *  class transImage
 *  transform the image and watermarking
 */
 class transImage {
    
    const ROTATEONEXIF = CONFIG_transImage::ROTATEONEXIF;
    const IMGTYPES     = CONFIG_transImage::IMGTYPES;
    const MAXSX        = CONFIG_transImage::MAXSX;
    const MAXSY        = CONFIG_transImage::MAXSY;
    
    private $ImgRes;
    private $orient = 1; // default 1 - normal orientation
    private $width;
    private $height;
    
    /**
     *  private constructur.
     *  For create object, use transImage::createFromFile()
     */
    private function __construct($ImgRes, $width, $height, $Orient = 1) {
        $this->ImgRes = $ImgRes;
        $this->orient = (self::ROTATEONEXIF)?$Orient:1;
        $this->width  = $width;
        $this->height = $height;
        $this->ImgRes = $ImgRes;
    }
    
    public function __destruct(){
        imageDestroy($this->ImgRes);
    }
    
    /*
     * function createFromFile
     * @param string $fileName
     * @param string $alarm
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
                        1  => 'gif',
                        2  => 'jpeg',
                        3  => 'png',
                        15 => 'wbmp',
                        16 => 'xbm'                        
                       );
        if( !isset($imgtypesarray[$imgNtype]) ||
            !in_array($imgNtype, explode(',',self::IMGTYPES)) ) {
            $alarm = 'image type not allowed';
            return false;
        }
        
        if( !($ImgRes = call_user_func('imageCreateFrom' . $imgtypesarray[$imgNtype], $fileName)) ) {
            $alarm = 'create image error';
            return false;
        }
        
        $Orient = 1;
        if(2 == $imgNtype && self::ROTATEONEXIF && ($exif = exif_read_data($fileName))
           && isset($exif['Orientation']) ) {
            $Orient = $exif['Orientation'];
        }
        
        $selfObj = new self($ImgRes, $width, $height, $Orient);
        
        # Fill a white background, if the format requires transparency
        if( !$selfObj->normalSize() && (1==$imgNtype || 3==$imgNtype) ) {
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
        $maxW = self::MAXSX; $maxH = self::MAXSY;
        
        if(6==$this->orient || 8==$this->orient || 5==$this->orient || 7==$this->orient) {
            $effH = $width; $effW = $height;
            $maxH = self::MAXSX; $maxW = self::MAXSY;
        }
        if($effW <= self::MAXSX && $effH <= self::MAXSY) {
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
                8 => 270,
                6 => 90,
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
    
    public function outJpeg() {
        header('Content-Type: image/jpeg');
        imagejpeg($this->ImgRes);
    }
    
    public function outPng() {
        header('Content-Type: image/png');
        imagepng($this->ImgRes);
    }
    
    public function outGif() {
        header('Content-Type: image/gif');
        imageGif($this->ImgRes);
    }
    
    /*
     * function save
     * @param string $toFile
     */
    public function save($toFile) {
        
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
