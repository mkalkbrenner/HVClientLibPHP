HVClientLibPHP
==============

An easy to use PHP library to connect to
[Microsoft HealthVault](https://www.healthvault.com/)
on top of
[HVRawConnectorPHP](https://github.com/mkalkbrenner/HVRawConnectorPHP/).
It adds a nicer object oriented programming interface and hides (most) of the
complicated XML parts in the HealthVault protocol.


Installation
------------

HVClientLibPHP depends on
[HVRawConnectorPHP](https://github.com/mkalkbrenner/HVRawConnectorPHP/).
If you simply use the latest development version of HVClientLibPHP from github
you have to ensure that HVRawConnectorPHP and all it's dependencies are
installed.

The latest stable version of HVClientLibPHP could be easily installed by pear.
In that case all it's dependencies will be installed automatically:

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

Some examples will follow later.

Meanwhile you can have a look at the demo_app source code.


Demo
----

The demo_app included in this repository currently offers two "features":
* It queries a user's HealthVault record for all
"[Things](http://developer.healthvault.com/pages/types/types.aspx)" and dumps the
raw XML content. By default it uses the US pre production instance of HealthVault.
* It lists all files uploaded to your selected health record and let you upload
additional files.

Simply put the HVClientLibPHP folder on a web server and access
"demo_app/index.php".


Licence
-------

[GPLv2](https://raw.github.com/mkalkbrenner/HVClientLibPHP/master/LICENSE.txt).


Sponsor
-------
[bio.logis](https://www.biologis.com) offers users of
[pgsbox.de](https://pgsbox.de) to upload diagnostic reports to HealthVault.
