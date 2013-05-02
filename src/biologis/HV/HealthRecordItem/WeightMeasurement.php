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
 * Class WeightMeasurement.
 * @see http://msdn.microsoft.com/en-us/library/dd726619.aspx
 */
class WeightMeasurement extends HealthRecordItemData {

  /**
   * @see http://msdn.microsoft.com/en-us/library/dd724265.aspx
   *
   * @param $timestamp
   * @param $weight
   * @return object File
   */
  public static function createFromData($timestamp, $weight) {
    $weightMeasurement = HealthRecordItemFactory::getThing('Weight Measurement');
    $weightMeasurement->setTimestamp('when', $timestamp);
    $weightMeasurement->getQp()->find('kg')->text($weight);
    return $weightMeasurement;
  }
}
