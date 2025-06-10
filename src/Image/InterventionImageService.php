<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Image;

use Intervention\Image\ImageManager;
use Intervention\Image\Image;
use Intervention\Image\Drivers\Gd\Driver;
use RuntimeException;

class InterventionImageService implements ImageResizerInterface
{
    const MAX_SIZE = 150;
    public function __construct(
        private readonly ImageManager $manager
    ) {
    }

    public function resize(string $path): string
    {
        $this->validateFileExists($path);

        try {
            $image = $this->loadImage($path);

            if ($this->shouldResize($image)) {
                $this->resizeImage($image);
            }

            return $image->encodeByPath();
        } catch (\Throwable $e) {
            throw new RuntimeException('Image processing error: ' . $e->getMessage(), 0, $e);
        }
    }
    private function validateFileExists(string $path): void
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new RuntimeException("The image file does not exist: {$path}");
        }
    }

    private function loadImage(string $path): Image
    {
        $manager = new ImageManager(Driver::class);
        return $manager->read($path);
    }

    private function shouldResize(Image $image): bool
    {
        return max($image->width(), $image->height()) > self::MAX_SIZE;
    }

    private function resizeImage(Image $image): void
    {
        if ($image->width() >= $image->height()) {
            $image->scale(width: self::MAX_SIZE);
        } else {
            $image->scale(height: self::MAX_SIZE);
        }
    }
}
