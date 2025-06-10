<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Storage;

class FtpStorage extends AbstractStorage
{
    private $fileConflictCommand;
    public function __construct(
        private readonly string $ftpHost,
        private readonly string $ftpUser,
        private readonly string $ftpPassword,
        private readonly string $ftpTargetDir
    ) {
    }

    public function save(string $filename, string $binary): void
    {
        $connection = $this->connectToFtp();
        $tmpPath = $this->createTempFileWithContents($binary);

        $remoteDir = rtrim($this->ftpTargetDir, '/');
        $this->ensureRemoteDirectoryExists($connection, $remoteDir);

        $existingFiles = $this->listRemoteFilenames($connection, $remoteDir);
        $uniqueFilename = $this->generateUniqueFilename($filename, $existingFiles);
        $remotePath = $remoteDir . '/' . $uniqueFilename;

        $this->uploadFile($connection, $remotePath, $tmpPath);
        unlink($tmpPath);
        ftp_close($connection);
    }

    private function connectToFtp()
    {
        $connection = ftp_connect($this->ftpHost);
        if (!$connection) {
            throw new StorageException("Unable to connect to FTP server: {$this->ftpHost}");
        }

        if (!ftp_login($connection, $this->ftpUser, $this->ftpPassword)) {
            ftp_close($connection);
            throw new StorageException("FTP login failed as {$this->ftpUser}");
        }

        ftp_pasv($connection, true);

        return $connection;
    }

    private function createTempFileWithContents(string $binary): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'thumb_');
        if ($tmpPath === false) {
            throw new StorageException("Failed to create a temporary file.");
        }

        if (file_put_contents($tmpPath, $binary) === false) {
            throw new StorageException("Failed to write contents to temporary file.");
        }

        return $tmpPath;
    }

    private function listRemoteFilenames($connection, string $directory): array
    {
        $list = ftp_nlist($connection, $directory);
        return is_array($list) ? array_map('basename', $list) : [];
    }

    private function uploadFile($connection, string $remotePath, string $localPath): void
    {
        $success = ftp_put($connection, $remotePath, $localPath, FTP_BINARY);
        if (!$success) {
            throw new StorageException("Filed to send file FTP: {$remotePath}");
        }
    }


    private function ensureRemoteDirectoryExists($connection, string $directory): void
    {
        $segments = explode('/', trim($directory, '/'));
        $path = '';

        foreach ($segments as $segment) {
            $path .= '/' . $segment;
            if (ftp_chdir($connection, $path)) {
                continue;
            }

            if (!ftp_mkdir($connection, $path)) {
                throw new StorageException("Failed to create directory: $path on FTP server");
            }
        }
    }

}