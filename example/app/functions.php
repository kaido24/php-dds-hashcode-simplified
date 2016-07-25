<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * Current file defines some helper functions for the application.
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

/**
 * Helper method for getting the POST parameter 'request_act'.
 *
 * @return string - Request act that was passed to index controller.
 */
function get_request_act () {
    return isset($_POST['request_act']) ? $_POST['request_act'] : null;
}

/**
 * Generates the Javascript that shows the green success message to user.
 *
 * @param $message - Success message.
 */
function show_success ($message) {
    $message = htmlspecialchars($message);
    echo("<script>
        document.getElementById('success').style.display = 'block';
        document.getElementById('success').innerHTML = '$message';
        </script>");
}

/**
 * Generates HTML that represents the red error message to show to user. Also logs the exception if logging is turned
 * on.
 *
 * @param $e - The Exception that the error is based on.
 */
function show_error_text ($e) {
    $code = $e->getCode();
    $message = (!!$code ? $code . ': ' : '') . $e->getMessage();
    debug_log($message);
    echo('<p class="alert alert-danger">' . $message . '</p>');
}

/**
 * Helper method for getting the DigiDocService session code from HTTP session.
 *
 * @return string - Session code of the current DigiDocService session.
 * @throws Exception - It is expected that if this method is called then dds session is started and session code is
 *                     loaded to HTTP session. If it is not so then an exception is thrown.
 */
function get_dds_session_code () {
    if (!isset($_SESSION['ddsSessionCode'])) {
        throw new Exception('There is no active session with DDS.');
    }

    return $_SESSION['ddsSessionCode'];
}

/**
 * Helper method for getting the name of the container currently handled. Used for example at the moment of downloading
 * the container to restore the original file name.
 *
 * @return string - File name of the container in the moment it was uploaded.
 * @throws Exception - It is expected that if this method is called then dds session is started and the original
 *                     container name is loaded to HTTP session. If it is not so then an exception is thrown.
 */
function get_original_container_name () {
    if (!isset($_SESSION['originalContainerName'])) {
        throw new Exception('There is no with files version of container, so the container can not be restored.');
    }

    return $_SESSION['originalContainerName'];
}

/**
 * Logging helper method. Logging that is done through this method can be turned of by setting the constant
 * HASHCODE_APP_LOGGING_ON to FALSE.
 *
 * @param $message - Message to be logged.
 */
function debug_log ($message) {
    if (HASHCODE_APP_LOGGING_ON) {
        error_log('[' . $_REQUEST['requestId'] . '] [' . session_id() . '] ' . $message);
    }
}