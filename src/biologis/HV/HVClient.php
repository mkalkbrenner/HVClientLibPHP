<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HVClient implements HVClientInterface, LoggerAwareInterface {

  private $appId;
  private $session;
  private $connector = NULL;
  private $logger = NULL;
  private $healthVaultPlatformUrl;
  private $healthVaultShellUrl;

  /**
   * @param string $appId
   *   HealthVault Application ID
   * @param array $session
   *   Session array, in most cases $_SESSION
   */
  public function __construct($appId, &$session) {
    $this->appId = $appId;
    $this->session = & $session;
    $this->setInstance(1, TRUE);
  }

  /**
   * @param string $thumbPrint
   *   Certificate thumb print
   * @param string $privateKey
   *   Private key as string or file path to load private key from
   * @param string $country
   *   TODO reference to Microsoft documentation for valid countries
   * @param string $languages
   *   TODO reference to Microsoft documentation for valid languages
   */
  public function connect($thumbPrint = NULL, $privateKey = NULL, $country = NULL, $language = NULL) {
    if (!$this->logger) {
      $this->logger = new NullLogger();
    }

    if (!$this->connector) {
      $this->connector = new HVRawConnector($this->appId, $thumbPrint, $privateKey, $this->session);
      $this->connector->setLogger($this->logger);
    }

    $this->connector->setHealthVaultPlatform($this->healthVaultPlatformUrl);

    if ($country) {
      $this->connector->setCountry($country);
    }

    if ($language) {
      $this->connector->setLanguage($language);
    }

    $this->connector->connect();
  }

  public function disconnect() {
    unset($this->session['healthVault']);
    unset($this->connector);
    $this->connection = NULL;
  }

  public function getAuthenticationURL($redirectUrl = '', $actionQs = array()) {
    return HVRawConnector::getAuthenticationURL($this->appId, $this->session, $this->healthVaultShellUrl, $redirectUrl, $actionQs);
  }

  public function getPersonInfo() {
    if ($this->connector) {
      $this->connector->authenticatedWcRequest('GetPersonInfo');
      $qp = $this->connector->getQueryPathResponse();
      $qpPersonInfo = $qp->find('person-info');
      if ($qpPersonInfo) {
        return new PersonInfo(qp('<?xml version="1.0"?>' . $qpPersonInfo->xml(), NULL, array('use_parser' => 'xml')));
      }
    }
    else {
      throw new HVClientNotConnectedException();
    }
  }

  public function getThings($thingNameOrTypeId, $recordId, $options = array()) {
    if ($this->connector) {
      $typeId = HealthRecordItemFactory::getTypeId($thingNameOrTypeId);

      $options += array(
        'group max' => 30,
      );

      $this->connector->authenticatedWcRequest(
        'GetThings',
        '3',
        '<group max="' . $options['group max'] . '"><filter><type-id>' . $typeId . '</type-id></filter><format><section>core</section><xml/></format></group>',
        array('record-id' => $recordId)
      );

      $things = array();
      $qp = $this->connector->getQueryPathResponse();
      $qpThings = $qp->branch()->find('thing');
      foreach ($qpThings as $qpThing) {
        $things[] = HealthRecordItemFactory::getThing(qp('<?xml version="1.0"?>' . $qpThing->xml(), NULL, array('use_parser' => 'xml')));
      }

      return $things;
    }
    else {
      throw new HVClientNotConnectedException();
    }
  }

  public function putThings($things, $recordId) {
    if ($this->connector) {
      $payload = '';

      if($things instanceof HealthRecordItemData) {
        $things = array($things);
      }

      foreach($things as $thing) {
        $payload .= $thing->getItemXml();
      }

      $this->connector->authenticatedWcRequest(
        'PutThings',
        '1',
        $payload,
        array('record-id' => $recordId)
      );
    }
    else {
      throw new HVClientNotConnectedException();
    }
  }

  /**
   * Set HealthVault instance to use for requests.
   * @see http://msdn.microsoft.com/en-us/healthvault/jj127014
   * @see http://msdn.microsoft.com/en-us/library/dn269023
   *
   * @param int $instanceId
   *   1 = US Instance (default)
   *   2 = UK Instance
   * @param bool $ppe
   *   TRUE = Pre Production Environment
   */
  public function setInstance($instanceId = 1, $ppe = FALSE) {
    if (2 == $instanceId) {
      $this->healthVaultPlatformUrl = ($ppe) ?
        'https://platform.healthvault-ppe.co.uk/platform/wildcat.ashx' :
        'https://platform.healthvault.co.uk/platform/wildcat.ashx';
      $this->healthVaultShellUrl = ($ppe) ?
        'https://account.healthvault-ppe.co.uk/redirect.aspx' :
        'https://account.healthvault.co.uk/redirect.aspx';
    }
    else {
      $this->healthVaultPlatformUrl = ($ppe) ?
        'https://platform.healthvault-ppe.com/platform/wildcat.ashx' :
        'https://platform.healthvault.com/platform/wildcat.ashx';
      $this->healthVaultShellUrl = ($ppe) ?
        'https://account.healthvault-ppe.com/redirect.aspx' :
        'https://account.healthvault.com/redirect.aspx';
    }
  }

  /**
   * @deprecated
   * use setInstance() instead.
   */
  public function setHealthVaultAuthInstance($healthVaultAuthInstance) {
    $this->healthVaultShellUrl = $healthVaultAuthInstance;
  }

  /**
   * @deprecated
   * use getShellUrl() instead.
   */
  public function getHealthVaultAuthInstance() {
    return $this->getShellUrl();
  }

  public function getShellUrl() {
    return $this->healthVaultShellUrl;
  }

  /**
   * @deprecated
   * use setInstance() instead.
   */
  public function setHealthVaultPlatform($healthVaultPlatform) {
    $this->healthVaultPlatformUrl = $healthVaultPlatform;
  }

  /**
   * @deprecated
   * use getPlatformUrl() instead.
   */
  public function getHealthVaultPlatform() {
    return $this->getPlatformUrl();
  }

  public function getPlatformUrl() {
    return $this->healthVaultPlatformUrl;
  }

  public function setConnector(HVRawConnectorInterface $connector) {
    $this->connector = $connector;
  }

  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }

}

class HVClientException extends \Exception {}

class HVClientNotConnectedException extends \Exception {}
