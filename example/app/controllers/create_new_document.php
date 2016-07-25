<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for creating a new container from an uploaded datafile.
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
try {
    $file_upload_input_name = 'dataFile';
    //Check if the there were any errors on the first datafile upload.
    File_Helper::check_uploaded_file_for_errors($file_upload_input_name);
    $container_type = Doc_Helper::get_desired_container_type('containerType');
    // Start the Session with DDS
    $start_session_response = $dds->StartSession(array ('bHoldSession' => 'true'));
    $dds_session_code = $start_session_response['Sesscode'];

    // Create an empty container to DDS session.
    $format = $container_type['format'];
    $version = $container_type['version'];
    $container_short_type = $container_type['shortType'];

    $uploaded_file_name = basename($_FILES[$file_upload_input_name]['name']);
    // Following 2 parameters are necessary for the next potential requests.
    $_SESSION['ddsSessionCode'] = $dds_session_code;
    $_SESSION['originalContainerName'] = Doc_Helper::get_new_container_name($uploaded_file_name, $container_short_type);

    // Store the data file to a more permanent place
    $path_to_datafile = File_Helper::move_uploaded_file_to_upload_dir($file_upload_input_name);

    $dds->CreateSignedDoc(
        array (
            'Sesscode' => get_dds_session_code(),
            'Format'   => $format,
            'Version'  => $version
        )
    );

    // Add data file as HASHCODE to the container in DDS session
    $datafile_mime_type = $_FILES[$file_upload_input_name]['type'];
    Doc_Helper::add_datafile_via_dds($path_to_datafile, $datafile_mime_type);

    // Get the HASHCODE container from DDS
    $get_signed_doc_response = $dds->GetSignedDoc(array ('Sesscode' => get_dds_session_code()));
    $container_data = $get_signed_doc_response['SignedDocData'];
    if (strpos($container_data, 'SignedDoc') === false) {
        $container_data = base64_decode($container_data);
    }

    // Create container with datafiles on the local server disk so that there would be one with help of which it is possible
    // to restore the container if download is initiated.
    $path_to_created_container = Doc_Helper::create_container_with_files(
        $container_data,
        array (new \SK\Digidoc\FileSystemDataFile($path_to_datafile))
    );
    File_Helper::delete_if_exists($path_to_datafile);

    // Show information to user about the uploaded document.
    include 'show_doc_info.php';
    show_success('Container created and datafile added.');

    debug_log("Container created, datafile added and session 
      started with hashcode form of container. DDS session ID: '$dds_session_code'.");

} catch (Exception $e) {
    show_error_text($e);
}