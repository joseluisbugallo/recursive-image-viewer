<?php

/**
 * Recursively scans a directory and returns an array of the images within
 *
 * This can be used as a stand alone application for viewing images or if you
 * are a web developer you can put this in the root of your site and easily
 * ahve a way to search all the images on your site from one page.
 *
 * @global array $types
 *     The image types to look for
 * @param string $dir
 *     The directory to scan
 * @param array $images
 *     An array to hold the images
 *
 * @return array
 *     The Images
 */
function get_images_recursive($dir, $images = array()) {
  global $types;

  // filetypes to display
  $types = array("jpeg", "jpg", "gif", "png");

  // add trailing slash if missing
  if (substr($dir, -1) != "/") {
    $dir .= "/";
  }

  $d = dir($dir) or die("get_images_recursive: Failed opening directory $dir for reading");

  while (false !== ($item = $d->read())) {
    // skip hidden files
    if ($item[0] == ".") {
      continue;
    }

    if (is_dir($dir . $item) && isset($_GET['all'])) {
      $this_dir = get_images_recursive($dir . $item);
      $images   = array_merge($images, $this_dir);
    }

    // check for image files
    $path     = $dir . $item;
    $mimetype = pathinfo($path, PATHINFO_EXTENSION);

    if (in_array(strtolower($mimetype), $types)) {
      $images[] = array(
        'path' => $path,
        'file' => "/" . $dir . $item,
        'size' => getimagesize($dir . $item)
      );
    }
  }
  $d->close();

  return $images;

}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>All The Things</title>
    <script src="jquery-1.11.3.min.js"></script>
    <script src="jquery.lazyload.js"></script>
    <style type="text/css">
      .container {
        display: flex;
        flex-wrap: wrap;
      }
      img {
        max-width: 300px;
      }
      .photo {
        max-width: 300px;
        margin: 12px;
        border: 1px solid #ccc;
        padding: 25px;
        font-size: 14px;
      }
      a{
        color:#4183c4;
        text-decoration:none;
        outline:none;
      }
      a:hover{
        text-decoration:underline;
      }
      a:active{
        outline:none;
      }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
  </head>
  <body>
    <div class="container">
      <?php
      $output = NULL;
      if (!empty($_GET['dir'])) {

        $dir = $_GET['dir'];
        // add trailing slash if missing
        if (substr($dir, -1) != "/") {
          $dir .= "/";
        }

        $d    = dir($dir) or die("get_images_recursive: Failed opening directory $dir for reading");
        $output .= "<ul>";
        while (false !== ($item = $d->read())) {
          // skip hidden files
          if ($item[0] == ".") {
            continue;
          }
          if (is_dir($dir . $item)) {
            $output .= "<li><a href=\"?dir=$dir$item\">images from /$dir$item</a></li>";
          }
        }
        $output .= "</ul>";

        // fetch image details
        $images = get_images_recursive($dir);

        $ratio = 0.3;
        foreach ($images as $img) {
          $width  = $img['size'][0] * $ratio;
          $height = $img['size'][1] * $ratio;

          // If you want to set the width and height in code delete the
          // img class above in the CSS and add the following line to the image tag
          // width=\"{$width}\" height=\"{$height}\"

          $output .= "<div class=\"photo\">";
          $output .= "<a target=\"_blank\" href=\"{$img['path']}\">";
          $output .= "<img class=\"lazy\" data-original=\"{$img['path']}\" alt=\"\"></a><br>\n";
          // display image file name as link
          $output .= "<a target=\"_blank\" href=\"{$img['path']}\">" . basename($img['file']) . "</a><br>\n";
          // display image dimenstions
          $output .= "({$img['size'][0]} x {$img['size'][1]} pixels)<br>\n";
          // display mime_type
          $output .= $img['size']['mime'];
          $output .= "</div>\n";
        }
      }
      // Link to the directories
      else {
        $d    = dir(__DIR__ . '/' . $dir) or die("get_images_recursive: Failed opening directory " . __DIR__ . '/' . $dir . " for reading");
        $output .= "<ul>";
        while (false !== ($item = $d->read())) {
          // skip hidden files
          if ($item[0] == ".") {
            continue;
          }
          if (is_dir($dir . $item)) {
            $output .= "<li><a href=\"?dir=$item\">images from /$item</a></li>";
          }
        }
        $output .= "</ul>";
      }

      $output = str_replace('<ul></ul>', '', $output);
      if (!empty($output)) {
        print $output;
      }
      ?>
    </div>
    <script type="text/javascript" charset="utf-8">
      $(function () {
        $("img.lazy").lazyload();
      });
    </script>
  </body>
</html>