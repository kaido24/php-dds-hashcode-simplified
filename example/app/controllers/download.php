<?php
try {
    debug_log('User started the download of the container.') .
    $path_to_original_container = File_Helper::get_upload_directory() . DIRECTORY_SEPARATOR . get_original_container_name();
    header("Content-Disposition: attachment; filename=\"" . get_original_container_name() . "\"");
    header('Content-Type: application/force-download');
    header('Content-Length: ' . filesize($path_to_original_container));
    header('Connection: close');
    readfile($path_to_original_container);
    die();
} catch (Exception $e) {
    echo $twig->render('header.twig', array('not-front' => TRUE));
    show_error_text($e);
    echo $twig->render('footer.twig');
}