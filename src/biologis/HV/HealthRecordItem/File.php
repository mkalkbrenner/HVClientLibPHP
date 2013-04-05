<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Class File.
 * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file.aspx
 * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file_members.aspx
 */
class File extends HealthRecordItemData {

  /**
   * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file.createfromfilepath.aspx
   *
   * @param $path
   * @return object File
   */
  public static function createFromFilePath($path) {
    if (is_readable($path)) {
      if ($content = file_get_contents($path)) {

        $file = HealthRecordItemFactory::getThing('File');
        $qp = $file->getQp();
        $qp->find('name')->text(basename($path))->top()
          ->find('size')->text(filesize($path))->top()
          ->find('content-type text')->text(mime_content_type($path))->top()
          ->find('data-other')->text(base64_encode($content))->top();

        return $file;
      }
    }
  }

  /**
   * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file.createfromstream.aspx
   *
   * @param resource $stream
   * @param $name
   * @param $contentType
   * @return object File
   */
  public static function createFromStream($stream, $name, $contentType) {
    if ($content = stream_get_contents($stream)) {

      $file = HealthRecordItemFactory::getThing('File');
      $qp = $file->getQp();
      $qp->find('name')->text($name)->top()
        ->find('size')->text(strlen($content))->top()
        ->find('content-type text')->text($contentType)->top()
        ->find('data-other')->text(base64_encode($content))->top();

      return $file;
    }
  }

}
