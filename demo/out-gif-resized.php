<?php
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = 'w8.JPG';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    
    $img->resize(300,200);
    $img->addWatermark(new waterMarkSmpl());
    
    $img->out(transImage::IMAGE_TYPE_GIF);
