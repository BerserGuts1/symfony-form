<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

interface StorageInterface
{
    public function save(string $filename, string $binary): void;
}
