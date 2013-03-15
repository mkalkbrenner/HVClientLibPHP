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

spl_autoload_register('HVClient::autoLoader');

class HVClient {

  private static $classNames = array();

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
    $this->connection->authentifiedWcRequest('GetPersonInfo');
    $qp = $this->connection->getQueryPathResponse();
    $qpPersonInfo = $qp->find(':root person-info');
    if ($qpPersonInfo) {
      return new PersonInfo(qp('<?xml version="1.0"?>' . $qpPersonInfo->xml(), NULL, array('use_parser' => 'xml')));
    }
  }

  public function getThings($thingId, $recordId, $options = array()) {
    $thingName = '';
    if (array_key_exists($thingId, HVRawConnector::$things)) {
      $thingName = $thingId;
      $thingId = HVRawConnector::$things[$thingId];
    }
    elseif (!in_array($thingId, HVRawConnector::$things)) {
      throw new Exception('Unknown Thing or ThingId: ' . $thingId);
    }
    else {
      $thingNames = array_flip(HVRawConnector::$things);
      $thingName = $thingNames[$thingId];
    }

    $options += array(
      'group max' => 30,
    );

    $this->connection->authentifiedWcRequest(
      'GetThings',
      '3',
      '<group max="' . $options['group max'] . '"><filter><type-id>' . $thingId . '</type-id></filter><format><section>core</section><xml/></format></group>',
      array('record-id' => $recordId)
    );

    $things = array();
    $className = HVClient::convertThingNameToClassName($thingName);
    $qp = $this->connection->getQueryPathResponse();
    $qpThings = $qp->branch()->find(':root thing');
    foreach ($qpThings as $qpThing) {
      $things[] = new $className(qp('<?xml version="1.0"?>' . $qpThing->xml(), NULL, array('use_parser' => 'xml')));
    }

    return $things;
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

  private static function convertThingNameToClassName($thingName) {
    if (!array_key_exists($thingName, HVClient::$classNames)) {
      $className = preg_replace('/[^a-zA-Z0-9]/', ' ', $thingName);
      HVClient::$classNames[$thingName] =
        preg_replace_callback('/\s+(\w)/', function($matches) {
         return strtoupper($matches[1]);
        }, $className);
    }

    return HVClient::$classNames[$thingName];
  }

  private static function convertClassNameToThingName($className) {
    if (in_array($className, HVClient::$classNames)) {
      $thingNames = array_flip(HVClient::$classNames);
      return $thingNames[$className];
    }
  }

  public static function autoLoader($class) {
    if (is_readable(__DIR__ . '/HVClientLib/' . $class . '.php')) {
      require('HVClientLib/' . $class . '.php');
    }
    elseif (HVClient::convertClassNameToThingName($class)) {
      class_alias('Thing', $class);
    }
  }
}
