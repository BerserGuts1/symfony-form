<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Service;

use JakubOlkowiczRekrutacjaSmartiveapp\Image\ImageResizerInterface;
use JakubOlkowiczRekrutacjaSmartiveapp\Storage\StorageInterface;

class ThumbnailGenerator
{
    public function __construct(
        private readonly ImageResizerInterface $resizer
    ) {}

    public function generate(StorageInterface $storage, string $sourcePath, string $targetFilename): void
    {
        $binary = $this->resizer->resize($sourcePath);
        $storage->save($targetFilename, $binary);
    }
}

