<?php
/*
 * jQuery File Upload Plugin PHP Class 5.11.2
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

namespace Mylen\JQueryFileUploadBundle\Services;
use stdClass;

use Mylen\JQueryFileUploadBundle\Services\Writer\IWriter;
use Mylen\JQueryFileUploadBundle\Services\Validator\IValidator;

class BaseFileHandler
{
    protected $options;
    protected $validator;
    protected $writer;

    protected $type = 200;
    protected $body = '';
    protected $header = array();
    protected $readfile = null;

    function __construct(IValidator $validator, IWriter $writer, $options=null)
    {
        $this->options = array(
            'script_url' => $this->getFullUrl().'/',
            'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
            'upload_url' => $this->getFullUrl().'/files/',
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
        );

        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }

        $this->validator = $validator;
        $this->writer = $writer;
    }

    /**
     * @param $filePath
     * @param string $version
     * @param null $filename
     * @param bool $delete
     * @return string URL of the image
     */
    protected function write($filePath, $version='original', $filename=null, $delete=false)
    {
        if (null === $filename) {
            $details  = pathinfo($filePath);
            $filename = $details['basename'];
        }

        // Write the file wherever we setup it
        $response = $this->writer->writeFile($filePath, $filename, $version);

        // Remove the temporary file
        if (false !== $response && true === $delete) {
            @unlink($filePath);
        }

        return $response;
    }

    protected function getFullUrl()
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      	return
    		($https ? 'https://' : 'http://').
    		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		($https && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function set_file_delete_url($file)
    {
        $file->delete_url = $this->options['script_url'].'?file='.rawurlencode($file->name);
        $file->delete_type = $this->options['delete_type'];

        if ($file->delete_type !== 'DELETE') {
            $file->delete_url .= '&_method=DELETE';
        }
    }

    protected function get_file_object($file_name)
    {
        $file_path = $this->options['upload_dir'].$file_name;

        if (is_file($file_path) && $file_name[0] !== '.') {
            $file       = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url  = $this->options['upload_url'].rawurlencode($file->name);

            $this->set_file_delete_url($file);
            return $file;
        }

        return null;
    }

    protected function get_file_objects()
    {
        $files = array();
        if (file_exists($this->options['upload_dir'])) {
            //throw new \Exception(sprintf('Folder "%s" to scan does not exist', $this->options['upload_dir']));
            $files = scandir($this->options['upload_dir']);
        }

        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            $files
        )));
    }

    protected function create_scaled_image($filePath, $options, $version='thumbnail', $file_name=null)
    {
        if (is_null($file_name)) {
            $details   = pathinfo($filePath);
            $file_name = $details['basename'];
        }

        $new_file_path                = $options['upload_dir'].$file_name;
        list($img_width, $img_height) = getimagesize($filePath);

        if (!$img_width || !$img_height) {
            return false;
        }

        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );

        if ($scale >= 1) {
            // Small image so the thumbnail is going to be the same as the original
            $scale = 1;
            //return false;
        }

        $new_width  = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img    = imagecreatetruecolor($new_width, $new_height);

        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img       = imagecreatefromjpeg($filePath);
                $write_image   = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ? $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                imagecolortransparent($new_img, imagecolorallocate($new_img, 0, 0, 0));
                $src_img       = imagecreatefromgif($filePath);
                $write_image   = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                imagecolortransparent($new_img, imagecolorallocate($new_img, 0, 0, 0));
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);

                $src_img       = imagecreatefrompng($filePath);
                $write_image   = 'imagepng';
                $image_quality = isset($options['png_quality']) ? $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }

        // var_dump($src_img, $new_file_path);
        $success = $src_img && imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):

        // Write the image to place and delete the temporary one
        if ($success) {
            $success = $this->write($new_file_path, $version, null, true);
        }

        //imagedestroy($src_img);
        imagedestroy($new_img);

        return $success;
    }

    protected function validate($uploaded_file, $file, $error, $index)
    {
        if ($error) {
            $file->error = $error;
            return false;
        }

        if (!$file->name) {
            $file->error = 'missingFileName';
            return false;
        }

        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = 'acceptFileTypes';
            return false;
        }

        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }

        if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size'])) {
            $file->error = 'maxFileSize';
            return false;
        }

        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = 'minFileSize';
            return false;
        }

        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            $file->error = 'maxNumberOfFiles';
            return false;
        }

        list($img_width, $img_height) = @getimagesize($uploaded_file);

        if (is_int($img_width)) {
            if ($this->options['max_width'] && $img_width > $this->options['max_width'] || $this->options['max_height'] && $img_height > $this->options['max_height']) {
                $file->error = 'maxResolution';
                return false;
            }
            if ($this->options['min_width'] && $img_width < $this->options['min_width'] || $this->options['min_height'] && $img_height < $this->options['min_height']) {
                $file->error = 'minResolution';
                return false;
            }
        }

        return true;
    }

    protected function upcount_name_callback($matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext   = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name)
    {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function trim_file_name($name, $type, $index)
    {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.'.$matches[1];
        }
        if ($this->options['discard_aborted_uploads']) {
            while(is_file($this->options['upload_dir'].$file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    protected function handle_form_data($file, $index)
    {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }

    protected function orient_image($file_path)
    {
      	$exif = @exif_read_data($file_path);

        if ($exif === false) {
            return false;
        }

      	$orientation = intval(@$exif['Orientation']);

      	if (!in_array($orientation, array(3, 6, 8))) {
      	    return false;
      	}

      	$image = @imagecreatefromjpeg($file_path);

      	switch ($orientation) {
        	  case 3:
          	    $image = @imagerotate($image, 180, 0);
          	    break;
        	  case 6:
          	    $image = @imagerotate($image, 270, 0);
          	    break;
        	  case 8:
          	    $image = @imagerotate($image, 90, 0);
          	    break;
          	default:
          	    return false;
      	}

      	$success = imagejpeg($image, $file_path);

      	// Free up memory (imagedestroy does not delete files):
      	@imagedestroy($image);

      	return $success;
    }

    /**
     * @param $uploaded_file  "/tmp/phpdxeEAA"
     * @param $name           "bob-ross-landscape-painting-281-2.jpg"
     * @param $size           108075
     * @param $type           "image/jpeg"
     * @param $error
     * @param null $index
     * @return \stdClass
     */
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null)
    {
        try{
            $file       = new stdClass();
            $file->name = $this->trim_file_name($name, $type, $index);
            $file->size = intval($size);
            $file->type = $type;

            // Generating random file name
            $uniqueId = uniqid('img', true);
            $uniqueId = preg_replace('#[^a-z0-9\-\_]#', '-', $uniqueId);
            $details  = pathinfo($file->name);
            $ext      = $details['extension'];


            if ($this->validate($uploaded_file, $file, $error, $index)) {

                $this->handle_form_data($file, $index);
                $file_path   = $this->options['upload_dir'].$uniqueId.'.'.$ext;
                clearstatcache();

                if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                    // multipart/formdata uploads (POST method uploads)
                    move_uploaded_file($uploaded_file, $file_path);
                } else {
                    // Non-multipart uploads (PUT method support)
                    file_put_contents(
                        $file_path,
                        fopen('php://input', 'r'),
                        0
                    );
                }

                // Get the size of the uploaded file
                $file_size = filesize($file_path);

                if ($file_size === $file->size) {

                    $file->signature = sha1_file($file_path);

                    // Validate and throw
                    $this->validator->isValid($file);

                    if ($this->options['orient_image']) {
                        $this->orient_image($file_path);
                    }

                } else if ($this->options['discard_aborted_uploads']) {
                    unlink($file_path);
                    $file->error = 'abort';
                }

                // Write the original and delete the temporary one
                $file->url = $this->write($file_path, 'original', null, true);

                // Set the size
                $file->size = $file_size;
                $this->set_file_delete_url($file);
            }
        }
        catch(\Exception $e)
        {
            $file->error = $e->getMessage();
            return $file;
        }

        return $file;
    }

    public function get()
    {
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }

        header('Content-type: application/json');
        echo json_encode($info);
    }

    public function post()
    {
        try{
            if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                return $this->delete();
            }

            $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
            $info   = array();

            if ($upload && is_array($upload['tmp_name'])) {

                // param_name is an array identifier like "files[]",
                // $_FILES is a multi-dimensional array:
                foreach ($upload['tmp_name'] as $index => $value) {
                    $info[] = $this->handle_file_upload(
                        $upload['tmp_name'][$index],
                        isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                        isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                        isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                        $upload['error'][$index],
                        $index
                    );
                }
            } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
                // param_name is a single object identifier like "file",
                // $_FILES is a one-dimensional array:
                $info[] = $this->handle_file_upload (
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                    isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
                    isset($upload['error']) ? $upload['error'] : null
                );
            }

            header('Vary: Accept');
            $json     = json_encode($info);
            $redirect = isset($_REQUEST['redirect']) ? stripslashes($_REQUEST['redirect']) : null;

            if ($redirect) {
                header('Location: '.sprintf($redirect, rawurlencode($json)));
                return;
            }

            if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                header('Content-type: application/json');
            } else {
                header('Content-type: text/plain');
            }

            echo $json;
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    public function delete()
    {
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->options['upload_dir'].$file_name;
        $success   = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);

        header('Content-type: application/json');
        echo json_encode($success);
    }

    protected function readfile($file_path)
    {
        $this->readfile = $file_path;
    }

    protected function body($str)
    {
        $this->body .= $str;
    }

    protected function header($str)
    {
        if (strchr($str, ':')) {
            $head = explode(':', $str);
            array_push($this->header, array($head[0] => $head[1]));
        } else {
            if (strstr($str, '403'))
                $this->type = 403;
            else if (strstr($str, '405'))
                $this->type = 405;
        }
    }

    public function getReadFile()
    {
        return $this->readfile;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }
}
