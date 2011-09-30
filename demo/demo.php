<?php
    
    require '../src/transImage.php';
    
    $imgfile = '../src/watermark.png';
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    
    //$img1 = $img->resize(300, 300, true);
    //$img1->outJpeg();
    
    //$img->addWatermark(new waterMark());
    //$img->addWatermark(new waterMark());
    //$img->resize(500,500);
    //$img->outPng();
    
    //$img->resize(500,500);
    $img->outPng();
    

