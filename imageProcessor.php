<?php
  /***************************************************
   * Only these origins are allowed to upload images *
   ***************************************************/
  $accepted_origins = array("http://localhost:1000", "localhost:2000", "https://ziki.hng.tech");

  /*********************************************
   * Change this line to set the upload folder *
   *********************************************/
  $imageFolder = "images/";

  reset ($_FILES);
  $temp = current($_FILES);
  if (is_uploaded_file($temp['tmp_name'])){
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      // same-origin requests won't set an origin. If the origin is set, it must be valid.
      if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
      header('Access-Control-Allow-Credentials: true');
      header('P3P: CP="There is no P3P policy."');


      } else {
        header("HTTP/1.1 403 Origin Denied");
        return;
      }
    }


    // Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.1 400 Invalid file name.");
        return;
    }

    // Verify extension
    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
        header("HTTP/1.1 400 Invalid extension.");
        return;
    }
       $ext = pathinfo($temp['name'], PATHINFO_EXTENSION);
      $uniqueImageName = uniqid();
    // Accept upload if there was no origin, or if it is an accepted origin
    $filetowrite = $imageFolder . $uniqueImageName.'.'.$ext;
    move_uploaded_file($temp['tmp_name'], $filetowrite);

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    echo json_encode(array('location' => $filetowrite));
  } else {
    // Notify editor that the upload failed
    header("HTTP/1.1 500 Server Error");
  }
