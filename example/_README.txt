In the example folder is the example application of the PHP hashcode library.
It is created to demonstrate the ability of DigiDocService to add signature(with ID card and Mobile ID), remove signature, add data file, remove datafile and do verification of a document without actual datafiles.

* Configuration parameters (for different environments) can be modified in commons/configuration.php file.
* Give permission to write to directory that is set in commons/configuration.php in constant HASHCODE_APP_UPLOAD_DIRECTORY (default is upload/ in example webapp root) for wwwrun user (or any other user under which apache operates).
* Register your ID-card signing certificate in www.openxades.org test-environment: http://www.openxades.org/upload_cert.php
* This application should be run over https, since web signing plugins require https.

* Application does some logging to PHP error log. Log format is '[requestId] [sessionId] message'.
    - If the user would like to log all requests and responses to and from DigiDocService then the constant LOG_ALL_DDS_REQUESTS_RESPONSES in commons/configuration.php file should be changed to 'true'.
    - If the user would like to turn off logging of the application completely then the constant HASHCODE_APP_LOGGING_ON in commons/configuration.php file should be changed to 'false'.

The following extensions have to be enabled in PHP: php_openssl, php_soap.dll.

The following commands can be used to start the built-in PHP server (since PHP version 5.5):

cd examples
php -S localhost:8000

NOTE: By registering your real Mobile-ID certificates into Test-DigiDocService you can use your own phone number (id-code) to test applications with Mobile-ID support.
Registration can be done on this page: https://www.openxades.org/MIDCertsReg/

