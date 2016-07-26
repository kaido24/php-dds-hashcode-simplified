<?php
try {
    $get_signed_doc_info_response = $dds->GetSignedDocInfo(array ('Sesscode' => get_dds_session_code()));
    $document_file_info = $get_signed_doc_info_response['SignedDocInfo'];
} catch (Exception $e) {
    show_error_text($e);
}
if (!isset($document_file_info)) {
  return;
}
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
    $template_vars = array(
        'document' =>  array(
            'format' => $document_file_info->Format,
            'version' => $document_file_info->Version,
        ),
        'data_files' => $data_files,
        'signatures' => $singatures
    );
    echo $twig->render('show_doc_info.twig', $template_vars);
?>
