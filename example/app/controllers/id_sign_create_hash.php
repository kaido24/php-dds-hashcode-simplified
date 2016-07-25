<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for starting the process of signing a document with ID card.
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
header('Content-Type: application/json');
$response = array ();
try {
    debug_log('User started the preparation of signature with ID Card to the container.');

    if (!isset($_POST['signersCertificateHEX']) /*|| !isset($_POST['signersCertificateID'])*/) {
        throw new Exception('There were missing parameters which are needed to sign with ID Card.');
    }

    // Let's prepare the parameters for PrepareSignature method.
    $prepare_signature_req_params['Sesscode'] = get_dds_session_code();
    $prepare_signature_req_params['SignersCertificate'] = $_POST['signersCertificateHEX'];
    $prepare_signature_req_params['SignersTokenId'] = '';

    if (isset($_POST['signersRole'])) {
        $prepare_signature_req_params['Role'] = $_POST['signersRole'];
    }
    if (isset($_POST['signersCity'])) {
        $prepare_signature_req_params['City'] = $_POST['signersCity'];
    }
    if (isset($_POST['signersState'])) {
        $prepare_signature_req_params['State'] = $_POST['signersState'];
    }
    if (isset($_POST['signersPostalCode'])) {
        $prepare_signature_req_params['PostalCode'] = $_POST['signersPostalCode'];
    }
    if (isset($_POST['signersCountry'])) {
        $prepare_signature_req_params['Country'] = $_POST['signersCountry'];
    }
    $prepare_signature_req_params['SigningProfile'] = '';

    // Invoke PrepareSignature.
    $prepare_signature_response = $dds->PrepareSignature($prepare_signature_req_params);

    // If we reach here then everything must be OK with the signature preparation.
    $response['signature_info_digest'] = $prepare_signature_response['SignedInfoDigest'];
    $response['signature_id'] = $prepare_signature_response['SignatureId'];
    $response['signature_hash_type'] = CertificateHelper::getHashType($response['signature_info_digest']);
    $response['is_success'] = true;
} catch (Exception $e) {
    $code = $e->getCode();
    $message = (!!$code ? $code . ': ' : '') . $e->getMessage();
    debug_log($message);
    $response['error_message'] = $message;
}

echo json_encode($response);