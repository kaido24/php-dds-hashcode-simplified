<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for adding a new datafile.
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
    $error_on_dds_add = false;
    try {
        $file_upload_input_name = 'dataFile';

        //Check if the there were any errors on the datafile upload.
        File_Helper::check_uploaded_file_for_errors($file_upload_input_name);

        // Store the data file to a more permanent place
        $path_to_datafile = File_Helper::move_uploaded_file_to_upload_dir($file_upload_input_name);

        // Add data file as HASHCODE to the container in DDS session
        $datafile_mime_type = $_FILES[$file_upload_input_name]['type'];

        Doc_Helper::add_datafile_via_dds($path_to_datafile, $datafile_mime_type);
    } catch (Exception $e) {
        show_error_text($e);
        $error_on_dds_add = true;
    }

    if (!$error_on_dds_add) {
        // Get the HASHCODE container from DDS
        $get_signed_doc_response = $dds->GetSignedDoc(array ('Sesscode' => get_dds_session_code()));
        $container_data = $get_signed_doc_response['SignedDocData'];
        if (strpos($container_data, 'SignedDoc') === false) {
            $container_data = base64_decode($container_data);
        }

        // Merge previously added datafiles to an array with the new datafile.
        $datafiles = Doc_Helper::get_datafiles_from_container();
        array_push($datafiles, new \SK\Digidoc\FileSystemDataFile($path_to_datafile));

        // Rewrite the local container with new content
        Doc_Helper::create_container_with_files($container_data, $datafiles);

        //Delete the datafile from server as it exists in the container anyway.
        File_Helper::delete_if_exists($path_to_datafile);
    }

    // Show information to user about the uploaded document.
    include 'show_doc_info.php';

    if (!$error_on_dds_add) {
        show_success('Datafile successfully added.');
        debug_log('User successfully added a datafile \'' . basename($path_to_datafile) . '\' to the container.');
    }
} catch (Exception $e) {
    if (isset($path_to_datafile)) {
        File_Helper::delete_if_exists($path_to_datafile);
    }
    show_error_text($e);
}