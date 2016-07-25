<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for starting the process of signing a document with Mobile ID.
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
header('Content-Type: application/json');
$response = array ();
try {
    if (!isset($_POST['subAct'])) {
        throw new Exception('There are missing parameters which are needed to sign with MID.');
    }

    $sub_act = $_POST['subAct'];

    if ($sub_act === 'START_SIGNING') {
        if (!isset($_POST['phoneNo']) || !isset($_POST['idCode'])) {
            throw new Exception('There were missing parameters which are needed to sign with MID.');
        }
        $phone_no = trim($_POST['phoneNo']);
        $id_code = trim($_POST['idCode']);
        debug_log(
            "User started the process of signing with MID. Mobile phone is '$phone_no' and ID code is '$id_code'."
        );

        // In actual live situation, the language could be taken from the users customer database for example.
        $language = 'EST';

        $mobile_sign_response = $dds->MobileSign(
            array (
                'Sesscode'                    => get_dds_session_code(),
                'SignerIDCode'                => $id_code,
                'SignerPhoneNo'               => $phone_no,
                'ServiceName'                 => DDS_MID_SERVICE_NAME,
                'AdditionalDataToBeDisplayed' => DDS_MID_INTRODUCTION_STRING,
                'Language'                    => $language,
                'MessagingMode'               => 'asynchClientServer',
                'ReturnDocInfo'               => false,
                'ReturnDocData'               => false
            )
        );

        $response['challenge'] = $mobile_sign_response['ChallengeID'];
    } else {
        $status_response = $dds->GetStatusInfo(
            array (
                'Sesscode'      => get_dds_session_code(),
                'ReturnDocInfo' => false,
                'WaitSignature' => false
            )
        );

        $status_code = $status_response['StatusCode'];
        debug_log("User is asking about the status of mobile signing. The status is '$status_code'.");
        $success = $status_code === 'SIGNATURE';
        if ($success) {
            $datafiles = Doc_Helper::get_datafiles_from_container();
            $get_signed_doc_response = $dds->GetSignedDoc(array ('Sesscode' => get_dds_session_code()));
            $container_data = $get_signed_doc_response['SignedDocData'];
            if (strpos($container_data, 'SignedDoc') === false) {
                $container_data = base64_decode($container_data);
            }

            // Rewrite the local container with new content
            Doc_Helper::create_container_with_files($container_data, $datafiles);

            $response['is_success'] = true;
        } elseif ($status_code !== 'REQUEST_OK' && $status_code !== 'OUTSTANDING_TRANSACTION') { //Process has finished unsuccessfully.
            $messages = $dds->get_mid_status_response_error_messages;
            if (isset($messages[$status_code])) {
                throw new Exception($messages[$status_code]);
            }
            throw new Exception("There was an error signing with Mobile ID. Status code is '$status_code'.");
        }
    }
} catch (Exception $e) {
    $code = $e->getCode();
    $message = (!!$code ? $code . ': ' : '') . $e->getMessage();
    debug_log($message);
    $response['error_message'] = $message;
}

echo json_encode($response);