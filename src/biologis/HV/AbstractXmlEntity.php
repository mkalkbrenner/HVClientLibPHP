<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

abstract class AbstractXmlEntity {

  protected $qp;
  protected $simpleXML = NULL;
  protected $payloadElement = '';
  protected $payload;

  public function __get($name) {
    if (is_null($this->simpleXML)) {
      $this->simpleXML = simplexml_load_string($this->getXML());
      if ($this->payloadElement) {
        $this->payload = $this->simpleXML->{$this->payloadElement};
      }
      else {
        $this->payload = $this->simpleXML;
      }
    }
    if (isset($this->payload->$name)) {
      return $this->payload->$name;
    }
    else {
      $name = str_replace('_', '-', $name);
      if (isset($this->payload->$name)) {
        return $this->payload->$name;
      }
    }

    return null;
  }


  public function getQp() {
    return $this->qp->top();
  }

  public function getXML() {
    return $this->qp->top()->xml();
  }
}
