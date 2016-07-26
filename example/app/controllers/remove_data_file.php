<?php
try {
    // Check if all required POST parameters are set for this operation.
    if (!isset($_POST['datafileId']) || !isset($_POST['datafileName'])) {
        throw new \Exception('There was an error. You need to start again.');
    }

    $datafile_id = $_POST['datafileId'];
    $datafile_name = $_POST['datafileName'];

    $error_on_dds_removal = false;
    try {
        // Remove the datafile from the container in DDS session.
        $dds->RemoveDataFile(
            array (
                'Sesscode'   => get_dds_session_code(),
                'DataFileId' => $datafile_id
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

        // Rewrite the container on the local disk with the remaining datafiles.
        $datafiles = Doc_Helper::remove_datafile($datafile_name);

        // Rewrite the local container with new content
        Doc_Helper::create_container_with_files($container_data, $datafiles);
    }

    if (!$error_on_dds_removal) {
        show_success('Datafile successfully removed.');
        debug_log("User successfully removed datafile '$datafile_name'.");
    }
} catch (Exception $e) {
    show_error_text($e);
}