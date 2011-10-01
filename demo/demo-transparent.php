<?php
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = '../src/watermarks/waterMarkSmpl.png';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    
    $img->out(transImage::IMAGE_TYPE_PNG);

