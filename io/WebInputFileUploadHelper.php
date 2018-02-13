<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:05
 */

namespace sinri\ark\io;


use sinri\ark\core\ArkHelper;

class WebInputFileUploadHelper
{
// upload process

    /**
     * @param string $name
     * @param callable $callback
     * @param string $error
     * @return bool|mixed
     */
    public function handleUploadFileWithCallback($name, $callback, &$error = '')
    {
        if (!isset($_FILES[$name])) {
            return false;
        }
        $error = $_FILES[$name]['error'];
        if ($error !== UPLOAD_ERR_OK) {
            return false;
        }
        $original_file_name = $_FILES[$name]['name'];
        $file_type = $_FILES[$name]['type'];
        $file_size = $_FILES[$name]['size'];
        $file_tmp_name = $_FILES[$name]['tmp_name'];

        // where you might need `move_uploaded_file`
        return call_user_func_array($callback, [$original_file_name, $file_type, $file_size, $file_tmp_name, $error]);
    }

    /**
     * @param $name
     * @param $callback
     * @param string $error
     * @return bool
     */
    public function handleUploadFilesWithCallback($name, $callback, &$error = '')
    {
        if (!isset($_FILES[$name]) && !is_array($_FILES[$name])) {
            return false;
        }
        $error = [];
        foreach ($_FILES[$name] as $index => $item) {
            $error[$index] = $item['error'];
            if ($error[$index] !== UPLOAD_ERR_OK) {
                return false;
            }
        }
        foreach ($_FILES[$name] as $index => $item) {
            $original_file_name = $item['name'];
            $file_type = $item['type'];
            $file_size = $item['size'];
            $file_tmp_name = $item['tmp_name'];

            // where you might need `move_uploaded_file`
            $done = call_user_func_array($callback, [$original_file_name, $file_type, $file_size, $file_tmp_name, $error]);
            if (!$done) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $code
     * @return string
     */
    public function descUploadFileError($code)
    {
        $phpFileUploadErrors = array(
            UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        );
        return ArkHelper::readTarget($phpFileUploadErrors, $code, 'Non System Error');
    }
}