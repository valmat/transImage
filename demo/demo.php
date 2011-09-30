<?php
    
    require '../src/transImage.php';
    
    $imgfile = 'w6.JPG';
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    
    //$img1 = $img->resize(300, 300, true);
    //$img1->outJpeg();
    
    $img->addWatermark(new waterMark());
    $img->outJpeg();
    

