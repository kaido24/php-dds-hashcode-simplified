<?php

try {
    //Check if the uploaded file is a DDOC or BDOC with the correct mime type and there was no errors in uploading the file.
    $file_upload_input_name = 'container';
    File_Helper::check_uploaded_file_for_errors(
        $file_upload_input_name,
        File_Helper::$allowed_digital_documents
    );
    // Create the hashcode version of the container and start the session with DDS.
    $hashcode_version_of_container = Doc_Helper::get_encoded_hashcode_version_of_container($file_upload_input_name);

    $start_session_response = $dds->StartSession(
        array (
            'bHoldSession' => 'true',
            'SigDocXML'    => $hashcode_version_of_container
        )
    );

    $dds_session_code = $start_session_response['Sesscode'];
    $original_container_name = $_FILES[$file_upload_input_name]["name"];

    // Following 2 parameters are necessary for the show_doc_info view and for the next potential requests.
    $_SESSION['ddsSessionCode'] = $dds_session_code;
    $_SESSION['originalContainerName'] = $original_container_name;

    // Try to move the uploaded file to user specified upload directory (configuration.php HASHCODE_APP_UPLOAD_DIRECTORY)
    File_Helper::move_uploaded_file_to_upload_dir($file_upload_input_name);


    show_success("Uploaded container parsed and session started.");
    debug_log("Uploaded container parsed and session started with hashcode form of container. DDS session ID: '$dds_session_code'."
    );

} catch (Exception $e) {
    show_error_text($e);
}