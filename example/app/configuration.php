<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * Current file defines a set of configuration parameters that are to be changed in the process of installation of this
 * example application in different environments.
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
 * @license       http://www.opensource.org/licenses/lgpl-license.php LGPL
 */

/**
 * DDS digidoc endpoint URL
 */
define('DDS_ENDPOINT_URL', 'https://tsp.demo.sk.ee/');

/**
 * Service name for the MID services in DDS(Will be displayed to users mobile phones screen during signing process)
 */
define('DDS_MID_SERVICE_NAME', 'Testimine');

/**
 * Explanatory message for the MID services in DDS.(Will be displayed to users mobile phones screen during signing
 * process)
 */
define('DDS_MID_INTRODUCTION_STRING', 'SK näidis hashcode allkirjastamine.');

/**
 * Directory where the uploaded files are copied and temporary files stored. SHOULD END WITH A DIRECTORY_SEPARATOR!!!
 */
define('HASHCODE_APP_UPLOAD_DIRECTORY', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app/upload' . DIRECTORY_SEPARATOR);

/**
 * If this is set to TRUE, then all SOAP envelopes used for communication with DigiDocService are logged.
 */
define('LOG_ALL_DDS_REQUESTS_RESPONSES', true);

/**
 * If this is set to FALSE, then all information logging in this application will be turned off.
 */
define('HASHCODE_APP_LOGGING_ON', true);

// TODO: Change to environment variable
define('SYSTEM_UPLOAD_PATH_ENVIRONMENT_VARIABLE', 'DDS_SYSTEM_UPLOAD_PATH');

define('HASHCODE_APP_TMP_DIR', HASHCODE_APP_UPLOAD_DIRECTORY . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR);