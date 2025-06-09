<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

class FtpStorage implements StorageInterface
{
    public function __construct(
        private readonly string $ftpHost,
        private readonly string $ftpUser,
        private readonly string $ftpPassword,
        private readonly string $ftpTargetDir
    ) {}

    public function save(string $filename, string $binary): void
    {
        // logika zapisu na FTP
    }
}
