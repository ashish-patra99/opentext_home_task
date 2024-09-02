<?php
// src/Service/FileUploaderService.php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * [Description FileUploaderService]
 */
class FileUploaderService
{

    /**
     * [Description for $targetDirectory]
     *
     * @var string
     */
    private string $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * [Upload files to server]
     *
     * @param array $files
     * 
     * @return array
     * 
     */
    public function upload(array $files): array
    {
        $fileNames = [];

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $file->move($this->getTargetDirectory(), $fileName);
            $fileNames[] = $this->getTargetDirectory().'/'.$fileName;
        }

        return $fileNames;
    }

    /**
     * [get target directory to upload the files from services.yml]
     *
     * @return string
     * 
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
