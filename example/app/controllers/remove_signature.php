<?php

try {
    // Check if all required POST parameters are set for this operation.
    if (!isset($_POST['signatureId'])) {
        throw new Exception('There was an error. You need to start again.');
    }

    $signature_id = $_POST['signatureId'];

    $error_on_dds_removal = false;
    try {
        // Remove the datafile from the container in DDS session.
        $dds->RemoveSignature(
            array (
                'Sesscode'    => get_dds_session_code(),
                'SignatureId' => $signature_id
            )
        );
    } catch (Exception $e) {
        show_error_text($e);
        $error_on_dds_removal = true;
    }

    if (!$error_on_dds_removal) {
        // Get the HASHCODE container from DDS
        $get_signed_doc_response = $dds->GetSignedDoc(array ('Sesscode' => get_dds_session_code()));
        $container_data = $get_signed_doc_response['SignedDocData'];
        if (strpos($container_data, 'SignedDoc') === false) {
            $container_data = base64_decode($container_data);
        }

        // Rewrite the container on the local disk.
        $datafiles = Doc_Helper::get_datafiles_from_container();

        // Rewrite the local container with new content
        Doc_Helper::create_container_with_files($container_data, $datafiles);
    }


    if (!$error_on_dds_removal) {
        show_success('Signature successfully removed.');
        debug_log("User successfully removed signature  with ID '$signature_id' from the container.");
    }

} catch (Exception $e) {
    show_error_text($e);
}