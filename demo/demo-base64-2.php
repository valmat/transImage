<?php
$memory_get_usage_start = (memory_get_usage()/1024);
    
    require '../src/transImage.php';
    require '../src/watermarks/waterMarkSmpl.php';
    
    $imgfile = 'w6.JPG';
    
    if( !($img = transImage::createFromFile($imgfile,$alarm) ) ) {
        echo $alarm;
    } else {
        $img->resize(800,600);
        $img1 = $img->resize(300,300, true);
        $img->addWatermark(new waterMarkSmpl());
        $img1->addWatermark(new waterMarkSmpl());
        
        $imguot  = $img->getString(transImage::IMAGE_TYPE_PNG);
        $imguot1 = $img1->getString();
        echo "<img  width='{$img->getWidth()}' height='{$img->getHeight()}' src='data:image/png;base64,$imguot'>";
        echo "<img  width='{$img1->getWidth()}' height='{$img1->getHeight()}' src='data:image/jpeg;base64,$imguot1'>";
    }
    
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'Κα<br>';
echo '<hr>memory peak_usage: '.(memory_get_peak_usage()/1024-$memory_get_usage_start) .'Κα<br>';

