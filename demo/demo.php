<?php
    
    require '../src/transImage.php';
    
    $imgfile = 'w8.JPG';
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    $img1 = $img->resize(100,100, true);
    $img1->outJpeg();
