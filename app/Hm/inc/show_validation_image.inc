<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

session_start();
  $string = $_SESSION['validation_number'];
  $BGred = 155;
  $BGgreen = 155;
  $BGblue = 155;
  if (eregi('[#]?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})', $settings['graph_bg_color'], $ret)) {
      $BGred = hexdec($ret[1]);
      $BGgreen = hexdec($ret[2]);
      $BGblue = hexdec($ret[3]);
  }

  $FGred = 0;
  $FGgreen = 0;
  $FGblue = 0;
  if (eregi('[#]?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})', $settings['graph_text_color'], $ret)) {
      $FGred = hexdec($ret[1]);
      $FGgreen = hexdec($ret[2]);
      $FGblue = hexdec($ret[3]);
  }

  if (($settings['advanced_graph_validation'] == 1 and function_exists('imagettfbbox'))) {
      $width = 0;
      $height = 0;
      $font_file = './fonts/font.ttf';
      $chars = [];
      $max_height = 0;
      for ($i = 0; $i < strlen($string); ++$i) {
          $font_size = rand($settings['advanced_graph_validation_min_font_size'], $settings['advanced_graph_validation_max_font_size']);
          $angle = rand(-10, 10);
          $box = imagettfbbox($font_size, $angle, $font_file, $string[$i]);
          $c_height = max($box[1], $box[3], $box[5], $box[7]) - min($box[1], $box[3], $box[5], $box[7]);
          $c_width = max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]) + 1;
          $chars[$i] = ['size' => $font_size, 'angle' => $angle, 'width' => $c_width + 2, 'height' => $c_height];
          $width += $c_width + 2;
          if ($height < $c_height) {
              $height = $c_height;
              continue;
          }
      }

      $im = imagecreate($width + 4, $height + 4);
      $background_color = imagecolorallocate($im, $BGred, $BGgreen, $BGblue);
      $text_color = imagecolorallocate($im, $FGred, $FGgreen, $FGblue);
      for ($i = 0; $i < 1000; ++$i) {
          imagesetpixel($im, rand(0, $width + 4), rand(0, $height + 4), $text_color);
      }

      $x = 4;
      for ($i = 0; $i < strlen($string); ++$i) {
          $font_size = 12;
          $box = imagettfbbox($font_size, $angle, $font_file, $string[$i]);
          $c_height = $height;
          $c_width = $chars[$i]['width'];
          $y = floor($c_width / 4);
          imagettftext($im, $chars[$i]['size'], $chars[$i]['angle'], $x + rand(0 - $y, $y), $c_height - 3 + rand(-3, 3), $text_color, $font_file, $string[$i]);
          $x += $c_width;
      }

      header('Content-type: image/png');
      imagepng($im);
      imagedestroy($im);
  } else {
      header('Content-type: image/png');
      $im = @imagecreate(@imagefontwidth(5) * @strlen($string) + 2, @imagefontheight(5) + 2);
      $background_color = imagecolorallocate($im, $BGred, $BGgreen, $BGblue);
      $text_color = imagecolorallocate($im, $FGred, $FGgreen, $FGblue);
      imagestring($im, 5, 1, 1, $string, $text_color);
      imagepng($im);
      imagedestroy($im);
  }
