<?php
/**
 * This is the example web application that demonstrates how to handle hashcode containers together with hashcode
 * PHP library and DigiDocService.
 *
 * The action for initiating a download of the container in session.
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