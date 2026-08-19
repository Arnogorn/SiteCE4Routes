<?php

namespace App\Service;

use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;

class Uploader
{


    /**
     * @param bool $cover true : recadre en carré $width x $height (avatars/portraits).
     *                     false : réduit uniquement si besoin en conservant les proportions,
     *                     sans recadrage (photos de galerie affichées en grand format).
     */
    public function save(UploadedFile $file, string $name, string $directory, int $width = 250, int $height = 250, bool $cover = true): string
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
            if ($cover) {
                $image->cover($width, $height);
            } else {
                $image->scaleDown($width, $height);
            }
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