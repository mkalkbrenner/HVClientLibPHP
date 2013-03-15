<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

class Thing extends AbstractXmlPopo {

  public function __construct(QueryPath $qp = NULL) {
    if (is_null($qp)) {
      // TODO create qp from scratch for putThings()
    }
    else {
      $this->qp = $qp;
    }

    $this->payloadElement = 'data-xml';
  }
}
