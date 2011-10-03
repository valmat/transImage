<?php
    
    require '../src/transImage.php';
    
    $imgfile = 'w6.JPG';
    
    if( !($img = transImage::createFromThumb($imgfile,$alarm) ) ) {
        echo $alarm;
    } else {
        $img->resize(60, 60);
        $img->out(transImage::IMAGE_TYPE_PNG);
        //$img->out();
    }
    
    