<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Image;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use RuntimeException;

class InterventionImageService implements ImageResizerInterface
{
    const MAX_SIZE = 150;
    public function __construct(
        private readonly ImageManager $manager
    ) {}

    public function resize(string $path): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new RuntimeException("The image file does not exist: {$path}");
        }

        try {
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($path);

            $image->scale(width: 300);
            return $image->encodeByPath();

        } catch (\Throwable $e) {
            throw new RuntimeException('Image processing error: ' . $e->getMessage());
        }
    }
}
