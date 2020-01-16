<?php
// header("Content-Type: image/jpg");

function convertRGBtoCMYK($image, $rgb_folder, $cmyk_folder) {
  try {
    $image_filepath = __DIR__ . '/images/' . $rgb_folder . $image;
    $i = new Imagick($image_filepath);

    if($i->getImageColorSpace() == 13 || $i->getImageColorSpace() == 1) {
      $profiles = $i->getImageProfiles('*', false);
      $has_icc_profile = (array_search('icc', $profiles) !== false);
      if($has_icc_profile === false) {
        $icc_rgb = file_get_contents(__DIR__  . '/icc/AdobeRGB1998.icc');
        $i->profileImage('icc', $icc_rgb);
        unset($icc_rgb);
      }
    }

    $icc_cmyk = file_get_contents(__DIR__ . '/icc/USWebCoatedSWOP.icc');
    $i->profileImage('icc', $icc_cmyk);
    unset($icc_cmyk);

    // $i->stripImage();
    $i->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
    $i->setImageResolution(300, 300);
    // $i->setImageFormat('tiff');
    $i->writeImage(__DIR__ . '/images/' . $cmyk_folder . $image);

    // To output the image on a web browser: uncomment following line and header(), and call convertRGBtoCMYK()
    // echo $i->getImageBlob();
    $i->destroy();
    return true;
  } catch(Exception $e) {
    // echo 'Caught exception: ' . $e->getMessage();
    return false;
  }
}