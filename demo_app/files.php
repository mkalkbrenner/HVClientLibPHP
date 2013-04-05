<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

use biologis\HV\HVClient;
use biologis\HV\HealthRecordItem\File;
use biologis\HV\HVRawConnectorUserNotAuthenticatedException;
use biologis\HV\HVRawConnectorAuthenticationExpiredException;

require '../vendor/autoload.php';

$appId = file_get_contents('app.id');

session_start();

ob_start();

print "Connecting HealthVault ...<br><hr>";
ob_flush();

$hv = new HVClient(
  $appId,
  $_SESSION
);

try {
  ob_start();

  $hv->connect(file_get_contents('app.fp'), 'app.pem');

  $personInfo = $hv->getPersonInfo();

  $personId = $personInfo->person_id;
  $recordId = $personInfo->selected_record_id;

  print 'person-id: <b>' . $personId . '</b><br>';
  print 'name: <b>' . $personInfo->name . '</b><br>';
  print 'preferred-culture language: <b>' . $personInfo->preferred_culture->language . '</b><br>';
  print '<hr>';

  ob_flush();

  if (isset($_POST['submit']) && 'Upload' == $_POST['submit'] && !empty($_FILES['thefile']['tmp_name'])) {
    $stream = fopen($_FILES['thefile']['tmp_name'], 'r');
    $file = File::createFromStream($stream, $_FILES['thefile']['name'], $_FILES['thefile']['type']);
    fclose($stream);

    $hv->putThings($file, $recordId);
  }

  $things = $hv->getThings('File', $recordId);
  foreach ($things as $thing) {
    print $thing->file->name . '<br>';
  }
  print "<hr>";
  ob_flush();

}
catch (HVRawConnectorUserNotAuthenticatedException $e) {
  print "You're not authenticated! ";
  printAuthenticationLink();
}
catch (HVRawConnectorAuthenticationExpiredException $e) {
  print "Your authentication expired! ";
  printAuthenticationLink();
}
catch (Exception $e) {
  print $e->getMessage() . '<br>';
  print $e->getCode() . '<br>';
  printAuthenticationLink();
}


function printAuthenticationLink() {
  global $hv;

  print '<a href="' . $hv->getAuthenticationURL(
    'http' . (!empty($_SERVER["HTTP_SSL"]) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])
    . '">Authenticate</a>';
}

?>
<form enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
  <input name="thefile" type="file">
  <input type="submit" name="submit" value="Upload">
</form>