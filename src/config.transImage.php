<?php
################################################################################
/**
  *  [transImage]
  *  config for class  transImage
  */

    class CONFIG_transImage {
        /**
          * Rotate image canvas on Exif data: true/false
          */
        const ROTATEONEXIF = true;
        
        /**
          * allowed image types
          * see http://www.php.net/manual/ru/function.exif-imagetype.php
          * 1:GIF, 2:JPEG, 3:PNG, 15:WBMP, 16:XBM
          */
        const IMGTYPES = '1,2,3,15,16';
        
        /**
          * max size on oX in pixeles
          */
        const MAXSX = 800;
        
        /**
          * max size on oY in pixeles
          */
        const MAXSY = 600;
    }
    
################################################################################
/**
  *  [waterMark]
  *  config for class  waterMark
  */
    class CONFIG_waterMark {
        /**
          * watermark sourse file in PNG format
          */
        const FILE = '../src/watermarks/waterMarkSmpl.png';
        
        /**
          * position at the top in px.
          * If negative, it is measured from the bottom 
          */
        const TOPPOS = -10;
        
        /**
          * position at the left in px.
          * If negative, it is measured from the right
          */
        const LEFTPOS = 20;
    }
    
?>