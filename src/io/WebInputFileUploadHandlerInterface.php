<?php


namespace sinri\ark\io;


abstract class WebInputFileUploadHandlerInterface
{
    /**
     * Handle a single file uploaded.
     * For a group of file uploaded once, it would be executed for each
     *
     * @param string $original_file_name
     * @param string $file_type
     * @param int $file_size
     * @param string $file_tmp_name
     * @param string &$error
     * @return bool
     */
    abstract public function handle($original_file_name, $file_type, $file_size, $file_tmp_name, &$error);
}