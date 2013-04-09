HVClientLibPHP
==============

An easy to use PHP library to connect to
[Microsoft® HealthVault™](https://www.healthvault.com/)
on top of
[HVRawConnectorPHP](https://github.com/mkalkbrenner/HVRawConnectorPHP/).
It adds a nicer object oriented programming interface and hides (most) of the
complicated XML parts in the HealthVault protocol.


Installation
------------

HVClientLibPHP depends on
[HVRawConnectorPHP](https://github.com/mkalkbrenner/HVRawConnectorPHP/).

You can simply use composer to install HVRawConnectorPHP and it's dependencies.

To add HVClientLibPHP as a library in your project, add something like that to
the 'require' section of your `composer.json`:

```json
{
  "require": {
    "biologis/hv-client-lib": "dev-master"
  }
}
```

If composer complains about an unknown pear channel, add this to your `composer.json`:
```json
{
  "repositories": [
    {
      "type": "pear",
      "url": "http://pear.php.net"
    }
  ]
}
```

Earlier version of HVClientLibPHP could also be installed by pear including all
it's dependencies:

    pear channel-discover pear.biologis.com
    pear channel-discover pear.querypath.org
    pear install biologis/HVClient

This method will install HVClientLibPHP as a library, but without the
available demo application.


Status
------

HVClientLibPHP is not a full featured HealthVault SDK, but should provide all
the required stuff to create powerful HealthVault applications with PHP.

It can basically handle all
[Things](http://developer.healthvault.com/pages/types/types.aspx) already,
but over the time we will add some more convenience function to the representing
classes.

But the number of implemented
[Methods](http://developer.healthvault.com/pages/methods/methods.aspx) is very
limited at the moment (the essential ones are available):
* GetPersonInfo
* GetThings
* PutThings

If you need more and understand the available
[Documentation](http://developer.healthvault.com/default.aspx), you can always
use HVRawConnectorPHP directly. In that case you should ideally contribute your
work to let HVClientLibPHP grow faster.


Usage
-----

This is a simple example to display all weight measurements:

```php
$hv = new HVClient($yourAppId, $_SESSION);
$hv->connect($yourCertThumbPrint, $yourPrivateKey);

$personInfo = $hv->getPersonInfo();
$recordId = $personInfo->selected_record_id;

$things = $hv->getThings('Weight Measurement', $recordId);
foreach ($things as $thing) {
  print $thing->weight->value->kg;
}
```

Connect a different HealthVault™ instance using a different country and
language:

```php
$hv = new HVClient($yourAppId, $_SESSION);

$hv->setHealthVaultPlatform(
  'https://platform.healthvault-ppe.co.uk/platform/wildcat.ashx');
$hv->setLanguage('de');
$hv->setCountry('DE');

$hv->connect($yourCertThumbPrint, $yourPrivateKey);
```

To connect your PHP based HealthVault™ app, your app needs to authorized by
the user and the user himself needs to be authenticated against HealthVault™.
If any of these requirements are not met, HVClientLibPHP throws corresponding
exceptions. In this case you can create a link that takes the user to
HealthVault™ to authenticate himself and to authorize your app and takes him
back to your site afterwards:

```php
$hv = new HVClient($yourAppId, $_SESSION);

try {
  $hv->connect($yourCertThumbPrint, $yourPrivateKey);
  $personInfo = $hv->getPersonInfo();
}
catch (HVRawConnectorUserNotAuthenticatedException $e) {
 print '<a href="' .
   $hv->getAuthenticationURL($yourReturnURL) .
   ">Authenticate</a>';
}
```

For more examples have a look at the demo_app source code included in
HVClientLibPHP.

The HVClientLibPHP will never provide an API to register a new HealthVault™ app
or to guide you through the on-boarding process, because this could be easily
done using the
[Application Configuration Center](http://config.healthvault-ppe.com/).

So register your app there first and start coding afterwards.

The demo_app (aka Hello World) is already registered. For your first tests you
can also use it's credentials to start right away.


Demo
----

The demo_app included in this repository currently demonstrates two features:
* It queries a user's HealthVault record for all
"[Things](http://developer.healthvault.com/pages/types/types.aspx)" and dumps the
raw XML content.
* It lists all files uploaded to your selected health record and lets you upload
additional files.

By default it uses the US pre production instance of HealthVault.

To get started, follow the install instructions above and put the demo_app folder
on a web server and access "demo_app/index.php".


Licence
-------

[GPLv2](https://raw.github.com/mkalkbrenner/HVClientLibPHP/master/LICENSE.txt).


Sponsor
-------
[bio.logis](https://www.biologis.com) offers users of
[pgsbox.de](https://pgsbox.com)[pgsbox.com] and (https://pgsbox.de) a way to
upload their diagnostic reports to HealthVault.

