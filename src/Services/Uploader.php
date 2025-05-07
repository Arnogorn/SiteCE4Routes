<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;

class Uploader
{


    public function save(UploadedFile $file, string $name, string $directory): string
    {

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $newFileName = $name . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($directory, $newFileName);

        $fullPath = rtrim($directory, '/\\') . '/' . $newFileName;

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($fullPath);
            $image->resize(250, 300);
            $image->save($fullPath);
        } catch (\Exception $e) {

        }

        return $newFileName;
    }

    public function delete(string $filename, string $directory)
    {
        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;


        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
            return true;
        }

        return false;
    }


}