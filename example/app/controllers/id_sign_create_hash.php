<?php

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