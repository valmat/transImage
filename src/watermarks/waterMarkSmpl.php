<?php
 /**
 *  class waterMarkSimpl
 */
 class waterMarkSmpl implements waterMark {
    
    const FILE    = CONFIG_waterMark::FILE;
    const TOPPOS  = CONFIG_waterMark::TOPPOS;
    const LEFTPOS = CONFIG_waterMark::LEFTPOS;
        
    private $res;
    private $width;
    private $height;
    
    /**
     *  constructur.
     */
    public function __construct($file = self::FILE) {
        list($this->width, $this->height, $imgNtype) = getimagesize($file);
        $imgtypesarray = Array(1 => 'gif', 2 => 'jpeg', 3 => 'png',);
        if( $imgNtype > 3 ) {
            return false;
        }
        $this->res = call_user_func('imageCreateFrom' . $imgtypesarray[$imgNtype], $file);
    }
    
    public function __destruct(){
        imageDestroy($this->res);
    }
    
    /*
     * function set
     * set watermark on image $img 
     * @param $img - image resource identifier
     * @param int $width 
     * @param int $height
     */
    function set($img, $width = 0, $height = 0) {
        $posX = (self::LEFTPOS>0) ? self::LEFTPOS : ($width - $this->width  + self::LEFTPOS);
        $posY = (self::TOPPOS>0)  ? self::TOPPOS  : ($height - $this->height + self::TOPPOS);
        return imagecopy ($img, $this->res, $posX, $posY, 0, 0, $this->width, $this->height);
        
    }
    
 }
 