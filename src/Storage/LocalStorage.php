<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

class LocalStorage extends AbstractStorage
{
    public function __construct(
        private readonly string $targetDirectory
    ) {
    }

    public function save(string $filename, string $binary): void
    {
        $this->ensureDirectoryIsWritable();

        $existingFiles = $this->listLocalFilenames();
        $uniqueFilename = $this->generateUniqueFilename($filename, $existingFiles);
        $path = $this->getFullPath($uniqueFilename);

        if (file_put_contents($path, $binary) === false) {
            throw new StorageException("Failed to save the file: " . $path);
        }
    }

    private function ensureDirectoryIsWritable(): void
    {
        if (!is_dir($this->targetDirectory)) {
            if (!mkdir($this->targetDirectory, 0775, true)) {
                throw new StorageException("The destination directory could not be created: {$this->targetDirectory}");
            }
        }

        if (!is_writable($this->targetDirectory)) {
            throw new StorageException("No permissions to write to the directory: {$this->targetDirectory}");
        }
    }

    private function listLocalFilenames(): array
    {
        $files = scandir($this->targetDirectory);
        return is_array($files) ? array_filter($files, fn($file) => is_file($this->getFullPath($file))) : [];
    }

    private function getFullPath(string $filename): string
    {
        return rtrim($this->targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    }
}