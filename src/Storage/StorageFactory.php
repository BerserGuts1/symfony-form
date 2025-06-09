<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

class StorageFactory
{
    public function __construct(
        private readonly FtpStorage $ftpStorage,
        private readonly LocalStorage $localStorage
    ) {}

    public function create(string $type): StorageInterface
    {
        return match ($type) {
            'ftp' => $this->ftpStorage,
            'local' => $this->localStorage,
            default => throw new \InvalidArgumentException("Nieznany typ storage: $type"),
        };
    }
}
