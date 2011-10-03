<?php
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = 'w8.JPG';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo "<hr>$alarm";
    }
    
    //$img1 = $img->resize(300, 300, true);
    
    $img->addWatermark(new waterMarkSmpl());
    $img->resize(500,500);
    
    //$img->resize(150,150);
    //$img->out();
    //$img->out(transImage::IMAGE_TYPE_GIF);
    //$img->out(transImage::IMAGE_TYPE_PNG);
    
    $img->save('/tmp/t1.PNG', transImage::IMAGE_TYPE_PNG);
    $img->save('/tmp/t1.GIF', transImage::IMAGE_TYPE_GIF);
    $img->save('/tmp/t1.JPG');
    $img->resize(150,150);
    $img->out(transImage::IMAGE_TYPE_PNG);

