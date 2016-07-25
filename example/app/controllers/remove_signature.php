<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for removing a signature from the container in session.
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

    // Show information to user about the document.
    include 'show_doc_info.php';

    if (!$error_on_dds_removal) {
        show_success('Signature successfully removed.');
        debug_log("User successfully removed signature  with ID '$signature_id' from the container.");
    }

} catch (Exception $e) {
    show_error_text($e);
}