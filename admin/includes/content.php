<?php
/*create*/
//    $photo = new Photo();
//    $photo->title = "Sam";
//    $photo->description = "Lorem ipsum";
//    $photo->filename = "image.jpg";
//    $photo->type = "jpg";
//    $photo->size = "15";
//    $photo->save();
/*update*/
    $photo = Photo::find_by_id(2);
    $photo->title = "Tom";
    $photo->description = "Lorem ipsum";
    $photo->filename = "image.jpg";
    $photo->type = "jpg";
    $photo->size = "15";
    $photo->save();

/* alle photos */
    $photos = Photo::find_all();
    foreach($photos as $photo){
        echo $photo->title . "<br>";
    }

?>