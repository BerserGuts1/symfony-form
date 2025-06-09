<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Image;

interface ImageResizerInterface
{
    public function resize(string $path, int $maxSize): string;
}
