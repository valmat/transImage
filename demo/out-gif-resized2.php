<?php
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = 'w1.JPG';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    $img->addWatermark(new waterMarkSmpl());
    $img->resize(150,100);
    
    $img->out(transImage::IMAGE_TYPE_GIF);
