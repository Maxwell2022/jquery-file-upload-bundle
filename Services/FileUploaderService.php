<?php

namespace Mylen\JQueryFileUploadBundle\Services;

use Mylen\JQueryFileUploadBundle\Services\IFileUploaderService;
use Mylen\JQueryFileUploadBundle\Services\UploadHandler;

class FileUploaderService implements IFileUploaderService
{
    protected $fileBasePath;
    protected $webBasePath;
    protected $appFolder;

    protected $allowedExtensions;
    protected $sizes;
    protected $originals;
    protected $uploadHandlerFactory;

    public function __construct($fileBasePath, $webBasePath, $folder, $allowedExtensions, $sizes, $originals)
    {
        $this->fileBasePath = $fileBasePath;
        $this->webBasePath = $webBasePath;
        $this->appFolder = $folder;
        $this->allowedExtensions = $allowedExtensions;
        $this->sizes = $sizes;
        $this->originals = $originals;
    }

    /**
     * {@inheritdoc }
     */
    public function handleFileUpload()
    {
        // Build a regular expression like #\.(gif|jpg|jpeg|png)$#i
        $allowedExtensionsRegex = '#\.('.preg_quote(implode('|', $this->allowedExtensions)).'$#i';

        $sizes = (is_array($this->sizes)) ? $this->sizes : array();

        $filePath = $this->fileBasePath . '/' . $this->appFolder;
        $webPath = $this->webBasePath . '/' . $this->appFolder;

        foreach ($sizes as &$size) {
            $size['upload_dir'] = $filePath . '/' . $size['folder'] . '/';
            $size['upload_url'] = $webPath . '/' . $size['folder'] . '/';
        }

        $originals = $this->originals;

        $uploadDir = $filePath . '/' . $originals['folder'] . '/';

        foreach ($sizes as &$size) {
            @mkdir($size['upload_dir'], 0777, true);
        }

        @mkdir($uploadDir, 0777, true);

        return $this->getUploadHandlerFactory()->createUploadHandler(array(
            'upload_dir' => $uploadDir,
            'upload_url' => $webPath . '/' . $originals['folder'] . '/',
            'image_versions' => $sizes,
            'accept_file_types' => $allowedExtensionsRegex
            ),
            false
        );
    }
}
