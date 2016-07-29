<?php

require __DIR__.'/../../vendor/autoload.php';

use SK\Digidoc\BdocContainer;
use SK\Digidoc\Digidoc;
use SK\Digidoc\FileSystemDataFile;

// Start DDS session with bHoldSession flag set to true.
// call CreateSignedDoc to create empty bdoc container or pass
// existing container to DDS StartSession call.

// add datafile from local filesystem
$filename = 'sample.txt';
$hash = BdocContainer::datafileHashcode(file_get_contents($filename));
// call AddDatafile with $hash value

// assign contaier data from DDS to variable $ddsContainerData
$digidoc = new Digidoc();
$session = $digidoc->createSession();
$containerHashcodes = $session->containerFromString($ddsContainerData);
$container = $containerHashcodes->toDatafilesFormat(array(new FileSystemDataFile($filename)));

echo $container->toString();
// You can send container contents to user using $container->toString(),
// but it should be called before $session->end();

// clean up temporary files
$session->end();