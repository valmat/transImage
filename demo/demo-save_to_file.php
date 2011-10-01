<?php
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = 'w6.JPG';
    $toFilePrefix = '/tmp/transImage_';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    $wm = new waterMarkSmpl();
    
    
    $img1 = $img->resize(300, 200, true);
    $img1->addWatermark($wm);
    $toFile = $toFilePrefix . '200x100.jpg';
    if( $img1->save($toFile) ){
        echo "<hr>save $toFile successfully";
    } else {
        echo "<hr>save $toFile fail";
    }
    
    $img1 = $img->resize(50, 40, true);
    $toFile = $toFilePrefix . '50x40.gif';
    if( $img1->save($toFile, transImage::IMAGE_TYPE_GIF) ){
        echo "<hr>save $toFile successfully";
    } else {
        echo "<hr>save $toFile fail";
    }
    $img1->close();
    
    
    $img->addWatermark($wm);
    $img->resize(640,480);
    $toFile = $toFilePrefix . '640x480.png';
    if( $img->save($toFile, transImage::IMAGE_TYPE_PNG) ){
        echo "<hr>save $toFile successfully";
    } else {
        echo "<hr>save $toFile fail";
    }
        
        

