<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

/**
 * Class File.
 * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file.aspx
 * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file_members.aspx
 */
class HVClient_File extends HVClient_HealthRecordItemData {

  /**
   * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.file.createfromfilepath.aspx
   *
   * @param $path
   * @return object File
   */
  public static function createFromFilePath($path) {
    if (is_readable($path)) {
      if ($content = file_get_contents($path)) {

        $file = HVClient_HealthRecordItemFactory::getThing('File');
        $qp = $file->getQp();
        $qp->find(':root name')->text(basename($path));
        $qp->find(':root size')->text(filesize($path));
        $qp->find(':root content-type text')->text(mime_content_type($path));
        $qp->find(':root data-other')->text(base64_encode($content));

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

      $file = HVClient_HealthRecordItemFactory::getThing('File');
      $qp = $file->getQp();
      $qp->find(':root name')->text($name);
      $qp->find(':root size')->text(strlen($content));
      $qp->find(':root content-type text')->text($contentType);
      $qp->find(':root data-other')->text(base64_encode($content));

      return $file;
    }
  }

}
