<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

class LocalStorage implements StorageInterface
{
    public function __construct(
        private readonly string $targetDirectory
    ) {}

    public function save(string $filename, string $binary): void
    {
        $path = rtrim($this->targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!is_dir($this->targetDirectory) && !mkdir($this->targetDirectory, 0775, true)) {
            throw new StorageException('Nie udało się utworzyć katalogu docelowego: ' . $this->targetDirectory);
        }

        if (!is_writable($this->targetDirectory)) {
            throw new StorageException('Brak uprawnień do zapisu w katalogu: ' . $this->targetDirectory);
        }

        if (file_put_contents($path, $binary) === false) {
            throw new StorageException('Nie udało się zapisać pliku: ' . $path);
        }
    }
}
