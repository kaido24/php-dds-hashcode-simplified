<?php

try {
    // Check if there was any kind of error during MID signing.
    if (isset($_POST['error_message'])) {
        echo("<p class=\"alert alert-danger\">" . $_POST['error_message'] . '</p>');
    }

    if (!isset($_POST['error_message'])) {
        show_success('Signature successfully added.');
        debug_log('User successfully added a signature with Mobile ID to the container.');
    }

} catch (Exception $e) {
    show_error_text($e);
}