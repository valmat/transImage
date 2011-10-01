<?php
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = 'w8.JPG';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    
    //$img1 = $img->resize(300, 300, true);
    //$img1->outJpeg();
    
    $img->addWatermark(new waterMarkSmpl());
    //$img->resize(500,500);
    //$img->outPng();
    
    //$img->resize(150,150);
    $img->outPng();
    //$img->outJpeg();
    //$img->outGif();
    

