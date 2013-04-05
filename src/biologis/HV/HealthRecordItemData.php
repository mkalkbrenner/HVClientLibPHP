<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

use QueryPath\Query;

/**
 * Class HealthRecordItemData.
 * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.healthrecorditemdata.aspx
 */
class HealthRecordItemData extends AbstractXmlEntity {

  protected $typeId;

  public function __construct(Query $qp) {
    $this->qp = $qp;
    $this->typeId = $this->qp->top()->find('type-id')->text();
    $this->payloadElement = 'data-xml';
  }

  public function getTypeId() {
    return $this->typeId;
  }

  /**
   * @see http://msdn.microsoft.com/en-us/library/dd724732.aspx
   *
   * @param string $element
   * @return string
   */
  public function getItemXml($element = '') {
    $qpElement = $this->qp->top();

    if (!$element) {
      $element = 'thing';
    }
    else {
      $qpElement = $qpElement->branch()->find($element);
      $this->qp->top();
    }

    if ($qpElement) {
      return "<$element>" . $qpElement->innerXML() . "</$element>";
    }
    else {
      throw new Exception();
    }
  }
}
