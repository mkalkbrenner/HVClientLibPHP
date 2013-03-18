<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

/**
 * @see http://pear.php.net/package/Log
 */
require_once 'Log.php';

/**
 * @see https://github.com/mkalkbrenner/HVRawConnectorPHP
 * @see http://pear.biologis.com
 */
require_once 'HVRawConnector.php';
require_once 'HVClientLib/HealthRecordItemFactory.php';

spl_autoload_register('HVClient::autoLoader');


class HVClient {

  private $appId;
  private $session;
  private $connection;
  private $logger;

  private $healthVaultPlatform = 'https://platform.healthvault-ppe.com/platform/wildcat.ashx';
  private $healthVaultAuthInstance = 'https://account.healthvault-ppe.com/redirect.aspx';

  public function __construct($appId, &$session, Log $logger = NULL) {
    $this->appId = $appId;
    $this->session = & $session;
    $this->logger = is_null($logger) ? Log::singleton('null') : $logger;
  }

  public function connect($thumbPrint, $privateKeyFile) {
    $this->connection = new HVRawConnector($this->appId, $thumbPrint, $privateKeyFile, $this->session, $this->healthVaultPlatform, $this->logger);
  }

  public function getAuthenticationURL($redirectUrl) {
    return HVRawConnector::getAuthenticationURL($this->appId, $redirectUrl, $this->session, $this->healthVaultAuthInstance);
  }

  public function getPersonInfo() {
    $this->connection->authenticatedWcRequest('GetPersonInfo');
    $qp = $this->connection->getQueryPathResponse();
    $qpPersonInfo = $qp->find(':root person-info');
    if ($qpPersonInfo) {
      return new PersonInfo(qp('<?xml version="1.0"?>' . $qpPersonInfo->xml(), NULL, array('use_parser' => 'xml')));
    }
  }

  public function getThings($thingNameOrTypeId, $recordId, $options = array()) {
    $typeId = HealthRecordItemFactory::getTypeId($thingNameOrTypeId);

    $options += array(
      'group max' => 30,
    );

    $this->connection->authenticatedWcRequest(
      'GetThings',
      '3',
      '<group max="' . $options['group max'] . '"><filter><type-id>' . $typeId . '</type-id></filter><format><section>core</section><xml/></format></group>',
      array('record-id' => $recordId)
    );

    $things = array();
    $qp = $this->connection->getQueryPathResponse();
    $qpThings = $qp->branch()->find(':root thing');
    foreach ($qpThings as $qpThing) {
      $things[] = HealthRecordItemFactory::getThing(qp('<?xml version="1.0"?>' . $qpThing->xml(), NULL, array('use_parser' => 'xml')));
    }

    return $things;
  }

  public function putThings($things, $recordId) {
    $payload = '';

    if($things instanceof HealthRecordItemData) {
      $things = array($things);
    }

    foreach($things as $thing) {
      $payload .= $thing->getItemXml();
    }

    $this->connection->authenticatedWcRequest(
      'PutThings',
      '1',
      $payload,
      array('record-id' => $recordId)
    );
  }

  public function setHealthVaultAuthInstance($healthVaultAuthInstance) {
    $this->healthVaultAuthInstance = $healthVaultAuthInstance;
  }

  public function getHealthVaultAuthInstance() {
    return $this->healthVaultAuthInstance;
  }

  public function setHealthVaultPlatform($healthVaultPlatform) {
    $this->healthVaultPlatform = $healthVaultPlatform;
  }

  public function getHealthVaultPlatform() {
    return $this->healthVaultPlatform;
  }

  public static function autoLoader($class) {
    if (is_readable(__DIR__ . '/HVClientLib/' . $class . '.php')) {
      require(__DIR__ . '/HVClientLib/' . $class . '.php');
    }
  }

}
