<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

interface HVClientInterface {

  /**
   * @param string $thumbPrint
   *   Certificate thumb print
   * @param string $privateKey
   *   Private key as string or file path to load private key from
   */
  public function connect($thumbPrint = NULL, $privateKey = NULL);

  public function disconnect();

  public function getAuthenticationURL($redirectUrl = '');

  public function getPersonInfo();

  public function getThings($thingNameOrTypeId, $recordId, $options = array());

  public function putThings($things, $recordId);

  public function setHealthVaultAuthInstance($healthVaultAuthInstance);

  public function getHealthVaultAuthInstance();

  public function setHealthVaultPlatform($healthVaultPlatform);

  public function getHealthVaultPlatform();

  public function setConnector(HVRawConnectorInterface $connector);

}
