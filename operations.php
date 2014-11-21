<?php

namespace phpfs;

class operations {

  static public function deleteDirectoryRecursively($dir) {
    if (!file_exists($dir)) {
      return true;
    }
    if (!is_dir($dir)) {
      return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
        continue;
      }
      if (!self::deleteDirectoryRecursively("$dir/$item")) {
        return false;
      }
    }
    return rmdir($dir);
  }

  static public function fileSort($a, $b) {
    if ($a['isfolder'] && !$b['isfolder']) {
      return -1;
    }
    if (!$a['isfolder'] && $b['isfolder']) {
      return 1;
    }
    return (strtolower($a['name']) < strtolower($b['name'])) ? -1 : 1;
  }

  static public function getContentType($filefullpath) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    $ext = finfo_file($finfo,$filefullpath);
    finfo_close($finfo);
    return $ext;
  }

  static public function getFilesInDir($dir, $skipFilesMatchingPattern = null, $getRecursiveFolderSize = true) {
    $files = array();
    if ($handle = opendir($dir)) {
      while (false !== ($entry = readdir($handle))) {
        if (!is_null($skipFilesMatchingPattern) &&
                preg_match($skipFilesMatchingPattern, $entry)) {
          continue;
        }
        if ($entry != "." && $entry != "..") {
          $fullpath = $dir . $entry;
          $isdir = is_dir($fullpath);
          if ($getRecursiveFolderSize && $isdir) {
            $filesize = self::getSizeOfFiles($fullpath . '/');
          } else {
            $filesize = $isdir ? null : filesize($fullpath);
          }
          $filedata = array(
              'name' => $entry,
              'filemtime' => filemtime($fullpath),
              'size' => self::sizeTranslate($filesize),
              'isfolder' => $isdir);
          array_push($files, $filedata);
        }
      }
      closedir($handle);
    }
    return $files;
  }

  static public function getSizeOfFiles($dir) {
    $totsize = 0;
    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
        continue;
      }
      if (is_dir($dir . $item)) {
        $totsize += self::getSizeOfFiles($dir . $item . '/');
      } else {
        $totsize += filesize($dir . $item);
      }
    }
    return $totsize;
  }

  static public function sizeTranslate($filesize) {
    $array = array(
        'YB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
        'ZB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
        'EB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
        'PB' => 1024 * 1024 * 1024 * 1024 * 1024,
        'TB' => 1024 * 1024 * 1024 * 1024,
        'GB' => 1024 * 1024 * 1024,
        'MB' => 1024 * 1024,
        'KB' => 1024);
    if ($filesize <= 1024) {
      $filesize = $filesize . ' Bytes';
    }
    $precision = 1;
    foreach ($array as $name => $size) {
      if ($filesize > $size || $filesize == $size) {
        $filesize = round((round($filesize / $size * 100) / 100), $precision) . ' ' . $name;
      }
    }
    return $filesize;
  }

}
