<?php
$memory_get_usage_start = (memory_get_usage()/1024);
    
    require '../src/transImage.php';
    
    $imgfile = 'w6.JPG';
    
    if( !($img = transImage::createFromThumb($imgfile,$alarm, 60, 60) ) ) {
        echo $alarm;
    } else {
        //$img->resize(60, 60);
        $img->out(transImage::IMAGE_TYPE_PNG);
        //$img->out();
    }
    
    
//echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'Κα<br>';
//echo '<hr>memory peak_usage: '.(memory_get_peak_usage()/1024-$memory_get_usage_start) .'Κα<br>';