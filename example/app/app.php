<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * For configuration and usage information of this example application see _README.txt in example web applications
 * root folder.
 *
 * Current file services all the requests made to the example web app and acts as a controller.
 *
 * PHP version 5.3+
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package       DigiDocHashcodeExample
 * @version       1.0.0
 * @author        Tarmo Kalling <tarmo.kalling@nortal.com>
 * @license       http://www.opensource.org/licenses/lgpl-license.php LGPL
 */

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

// App entry point
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(get_request_act(), $recognized_post_request_acts, true)) {
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

    }

    Doc_Helper::persist_hashcode_session();

} else { // Default behavior is to show the index page.
  loadStartPageTemplate ($dds, $twig);
}
