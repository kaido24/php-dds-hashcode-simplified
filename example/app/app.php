<?php

date_default_timezone_set('Europe/Tallinn');
$_REQUEST['requestId'] = uniqid('sk_dds_hashcode', true);

require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/configuration.php';
require __DIR__.'/exception/FileException.php';
require __DIR__.'/functions.php';
require __DIR__.'/helpers/Doc_Helper.php';
require __DIR__.'/helpers/File_Helper.php';
require __DIR__.'/helpers/CertificateHelpers.php';
require __DIR__.'/DigiDocService/DigiDocService.php';

$loader = new Twig_Loader_Filesystem(__DIR__  . '/views');
$twig = new Twig_Environment($loader, array( 'debug' => true));
$twig->addExtension(new Twig_Extension_Debug());

session_start();

$dds = DigiDocService::Instance();

$recognized_post_request_acts = array (
    'PARSE_OLD_DOCUMENT',
    'CREATE_NEW_DOCUMENT',
    'ADD_DATAFILE',
    'REMOVE_DATA_FILE',
    'ID_SIGN_CREATE_HASH',
    'ID_SIGN_COMPLETE',
    'MID_SIGN',
    'MID_SIGN_COMPLETE',
    'REMOVE_SIGNATURE',
    'DOWNLOAD'
);

$supportedDigiDocActions = array (
    'PARSE_OLD_DOCUMENT',
    'CREATE_NEW_DOCUMENT',
    'ADD_DATAFILE',
    'REMOVE_DATA_FILE',
    'ID_SIGN_COMPLETE',
    'MID_SIGN_COMPLETE',
    'REMOVE_SIGNATURE'
);

// App entry point
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(get_request_act(), $recognized_post_request_acts, true)) {
   
    post_request_get_actions($supportedDigiDocActions, $dds, $twig);
    Doc_Helper::persist_hashcode_session();

} else { // Default behavior is to show the index page.
  loadStartPageTemplate ($dds, $twig);
}

function post_request_get_actions($supportedDigiDocActions, $dds, $twig) {
     // Some kind of document processing request has probably already been instantiated.
    // Following request_act-s return something else than text/html.
    $requestedAction = $_POST['request_act'];

    if ($requestedAction === 'DOWNLOAD') {
        require 'controllers/download.php';
    } elseif ($requestedAction === 'MID_SIGN') {
        require 'controllers/mid_sign.php';
    } elseif ($requestedAction === 'ID_SIGN_CREATE_HASH') {
        require 'controllers/id_sign_create_hash.php';
    } else {
        // Rest of the request_act-s all return text/html.
      loadDigiDocActionTemplates($supportedDigiDocActions, $requestedAction, $dds, $twig);
      include 'controllers/show_doc_info.php';
    }
}
/**
 * Check if there is open session then try to close it
 *
 * @param $dds
 *
 * @throws Exception
 */
function killDdsSession (DigiDocService $dds) {
    if (isset($_SESSION['ddsSessionCode'])) {
        // If the session data of previous dds session still exists we will initiate a cleanup.
        File_Helper::delete_if_exists(File_Helper::get_upload_directory());
        try {
            $dds->CloseSession(array ('Sesscode' => get_dds_session_code()));
            debug_log('DDS session \'' . get_dds_session_code() . '\' closed.');
        } catch (Exception $e) {
            debug_log('Closing DDS session ' . get_dds_session_code() . ' failed.');
        }
    }

    Doc_Helper::get_hashcode_session()->end(); // End the Hashcode container session.
    session_destroy(); // End the HTTP session.
}

function loadDigiDocActionTemplates($actionList, $requestedAction, $dds, $twig) {
    // Rest of the request_act-s all return text/html.

  echo $twig->render('header.twig', array('not-front' => TRUE));

    foreach ($actionList as $action) {
        if ($requestedAction === $action) {
            include __DIR__.'/controllers/'.strtolower($action).'.php';
            break;
        }
    }
  echo $twig->render('footer.twig');
}

/**
 * @param $dds
 */
function loadStartPageTemplate ($dds, $twig) {
  killDdsSession($dds);
  echo $twig->render('header.twig');
  echo $twig->render('default.twig');
  echo $twig->render('footer.twig');
}