<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for getting information about the document in DDS session and presenting it to user. This is usually
 * included after another action has completed.
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
    $get_signed_doc_info_response = $dds->GetSignedDocInfo(array ('Sesscode' => get_dds_session_code()));
    $document_file_info = $get_signed_doc_info_response['SignedDocInfo'];
} catch (Exception $e) {
    show_error_text($e);
}
if (!isset($document_file_info)) {
  return;
}
// General container info
$header_vars = array(
    'format' => $document_file_info->Format,
    'version' => $document_file_info->Version,
);
/*
*  Files/
*/
// If DataFileInfo exists return data else return false.
$data_files = array();
$DataFileInfo = isset($document_file_info->DataFileInfo) ? $document_file_info->DataFileInfo : FALSE;
if ($DataFileInfo) {
    $data_files = isset($DataFileInfo->Id) ? array($DataFileInfo) : $DataFileInfo;
}

// Signatures

// If SignatureInfo exists return data else return false.
$signatures_data = array();
$SignatureInfo = isset($document_file_info->SignatureInfo) ? $document_file_info->SignatureInfo : FALSE;
if ($SignatureInfo) {
    $signatures_data = isset($SignatureInfo->Id) ? array($SignatureInfo) : $SignatureInfo;
}
$singatures = array();
// Add some extra info for view.
foreach ($signatures_data as $signature) {
    $status = isset($signature->Status) ? $signature->Status : '';
    if ($status == 'OK') {
        if (isset($signature->Error) && $signature->Error->Category = 'WARNING') {
            $signature->alternative_info = 'WARNING(' . $signature->Error->Code . '): ' . $signature->Error->Description;
        }
    } elseif ($status == 'ERROR') {
        $signature->alternative_info = 'TECHNICAL(' . $signature->Error->Code . '): ' . $signature->Error->Description;
    }
    $singatures[] = $signature;
}

    echo $twig->render('show_doc_info_container_general.twig', $header_vars);
    echo $twig->render('doc_info_data_table_files.twig', array('data_files' => $data_files));
    echo $twig->render('doc_info_data_table_signatures.twig', array('signatures' => $singatures));
?>