<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

abstract class AbstractStorage implements StorageInterface
{
    protected function generateUniqueFilename(string $filename, array $existingFiles): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 1;
        $newName = $filename;

        while (in_array($newName, $existingFiles)) {
            $newName = sprintf('%s-%d.%s', $base, $counter++, $ext);
        }

        return $newName;
    }
}
