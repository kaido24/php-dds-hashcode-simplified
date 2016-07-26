<?php

try {
    // Check if there was any kind of error during ID Card signing.
    if (isset($_POST['error_message'])) {
        echo('<p class="alert alert-danger">' . $_POST['error_message'] . '</p>');
        if (!empty($_POST['signature_id'])) {
            // The fact that there has been an error and there is a signature ID means that there is a prepared
            // but not finalized signature in the session that needs to be removed.
            $dds->RemoveSignature(
                array ('Sesscode' => get_dds_session_code(), 'SignatureId' => $_POST['signature_id'])
            );
            debug_log(
                "Adding a signature to the container was not completed successfully so the prepared signature was removed from the container in DigiDocService session."
            );
        }
    } else {
        if (!isset($_POST['signature_value']) || !isset($_POST['signature_id'])) {
            throw new Exception('There were missing parameters which are needed to sign with ID Card.');
        }

        // Everything is OK. Let's finalize the signing process in DigiDocService.
        $dds->FinalizeSignature(
            array (
                'Sesscode'       => get_dds_session_code(),
                'SignatureId'    => $_POST['signature_id'],
                'SignatureValue' => $_POST['signature_value']
            )
        );

        // Rewrite the local container with new content
        $datafiles = Doc_Helper::get_datafiles_from_container();
        $get_signed_doc_response = $dds->GetSignedDoc(array ('Sesscode' => get_dds_session_code()));
        $container_data = $get_signed_doc_response['SignedDocData'];
        if (strpos($container_data, 'SignedDoc') === false) {
            $container_data = base64_decode($container_data);
        }

        Doc_Helper::create_container_with_files($container_data, $datafiles);
    }

    if (!isset($_POST['error_message'])) {
        show_success('Signature successfully added.');
        debug_log('User successfully added a signature with ID Card to the container.');
    }

} catch (Exception $e) {
    show_error_text($e);
}