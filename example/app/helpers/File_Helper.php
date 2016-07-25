<?php

/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * Class File_Helper - Different helper methods for handling file related stuff.
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
class File_Helper {

    /**
     * Array of allowed digital documents. Array keys are the file extensions and values are the corresponding arrays of
     * mime types.
     */
    public static $allowed_digital_documents = array (
        'ddoc'  => array ('application/x-ddoc'),
        'bdoc'  => array ('application/vnd.etsi.asic-e+zip', 'application/vnd.bdoc-1.0'),
        'asice' => array ('application/vnd.etsi.asic-e+zip', 'application/vnd.bdoc-1.0'),
        'sce'   => array ('application/vnd.etsi.asic-e+zip', 'application/vnd.bdoc-1.0')
    );

    /**
     * Checks if there is errors with the named file in the request.
     *
     * @param $input_name         - Name of the input used to upload the file.
     * @param $allowed_file_types - Map where keys are allowed file extensions and values are arrays of corresponding
     *                            allowed MIME types. If this is left as null then there is no restrictions in file and
     *                            mime types.
     *
     * @throws Exception - Throws an Exception with the corresponding message if there is a problem with the file
     *     upload.
     */
    public static function check_uploaded_file_for_errors ($input_name, $allowed_file_types = null) {
        if (($_FILES[$input_name]['error'] > 0)) {
            throw self::upload_error_code_to_exception($_FILES[$input_name]['error']);
        }
        if ($allowed_file_types != null) {
            $extension = self::get_uploaded_files_extension($input_name);
            if (!array_key_exists($extension, $allowed_file_types)) {
                throw new Exception('Uploaded file is in unsupported type.');
            }
            $mime_type = $_FILES[$input_name]['type'];
            if (!in_array($mime_type, $allowed_file_types[$extension])) {
                throw new Exception("Uploaded file has an unsupported mime type '$mime_type'.");
            }
        }
        $file_name = $_FILES[$input_name]['name'];
        debug_log("User uploaded file '$file_name' successfully.");
    }

    /**
     * Resolves possible upload errors to human readable messages.
     * http://php.net/manual/en/features.file-upload.errors.php
     *
     * @param $code - Upload error code.
     *
     * @return Exception - Corresponding exception.
     */
    private static function upload_error_code_to_exception ($code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'The uploaded file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Missing a temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Failed to write file to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = 'File upload stopped by extension';
                break;

            default:
                $message = 'Unknown upload error';
                break;
        }

        return new Exception($message, $code);
    }

    /**
     * Parses the uploaded containers file extension.
     *
     * @param $name - Name of the input used to upload the file.
     *
     * @return string - File extension as string.
     */
    public static function get_uploaded_files_extension ($name) {
        return self::parse_file_extension($_FILES[$name]['name']);
    }

    /**
     * Parses file extension. Splits file name by '.' and returns the second half lowered.
     *
     * @param $filename - File name or path.
     *
     * @return string - File extension as string.
     */
    public static function parse_file_extension ($filename) {
        $temp = explode('.', $filename);

        return strtolower(end($temp));
    }

    /**
     * Moves the uploaded file to upload directory specified in configuration.php HASHCODE_APP_UPLOAD_DIRECTORY constant
     *
     * @param $file_input_name - Name of the file input through which the file is uploaded.
     *
     * @return string - New location of the datafile.
     * @throws Exception - If there was a problem with moving the file to the user specified directory.
     */
    public static function move_uploaded_file_to_upload_dir ($file_input_name) {
        $filename = $_FILES[$file_input_name]['name'];
        $dir_path = self::get_upload_directory();

        if (!file_exists($dir_path) && !mkdir($dir_path)) {
            throw new FileException("There was a problem creating a directory '$dir_path' for uploaded file storage.");
        }

        $destination = $dir_path . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $destination)) {
            throw new FileException('There was a problem saving the uploaded file to disk.');
        }
        debug_log("Uploaded file moved to location '$destination'.");

        return $destination;
    }

    /**
     * Deletes a directory or a file if one exists on a given path.
     *
     * @param $path - Path to delete. WARNING! Deletes everything in this path recursively with its contents.
     */
    public static function delete_if_exists ($path) {
        if (!file_exists($path)) {
            return;
        }
        if (!is_dir($path)) {
            unlink($path);

            return;
        }

        foreach (glob($path . '/*') as $file) {
            if (is_dir($file)) {
                self::delete_if_exists($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function get_upload_directory () {
        $dir_path = HASHCODE_APP_UPLOAD_DIRECTORY . get_dds_session_code();

        debug_log('System variable name: '.SYSTEM_UPLOAD_PATH_ENVIRONMENT_VARIABLE);
        debug_log('System upload directory: '.getenv(SYSTEM_UPLOAD_PATH_ENVIRONMENT_VARIABLE));

        if (getenv(SYSTEM_UPLOAD_PATH_ENVIRONMENT_VARIABLE) !== false) {
            $dir_path = getenv(SYSTEM_UPLOAD_PATH_ENVIRONMENT_VARIABLE) . DIRECTORY_SEPARATOR . get_dds_session_code();
        }

        debug_log("Upload directory: '$dir_path'.");

        return $dir_path;
    }

} 