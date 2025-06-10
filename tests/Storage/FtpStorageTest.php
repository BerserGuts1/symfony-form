<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Tests\Storage;

use PHPUnit\Framework\TestCase;
use JakubOlkowiczRekrutacjaSmartiveapp\Storage\FtpStorage;
use JakubOlkowiczRekrutacjaSmartiveapp\Tests\Config\FtpTestConfig;

final class FtpStorageTest extends TestCase
{
    public function testFileIsUploadedToFtp(): void
    {
        $filename = 'ftp_test_' . uniqid() . '.txt';
        $content = 'Test content for FTP.';

        $storage = new FtpStorage(
            ftpHost: FtpTestConfig::host(),
            ftpUser: FtpTestConfig::user(),
            ftpPassword: FtpTestConfig::password(),
            ftpTargetDir: FtpTestConfig::targetDir()
        );

        $storage->save($filename, $content);
        $ftp = ftp_connect(FtpTestConfig::host());
        $this->assertNotFalse($ftp, 'Could not connect to FTP');

        $this->assertTrue(ftp_login($ftp, FtpTestConfig::user(), FtpTestConfig::password()));
        ftp_pasv($ftp, true);

        $files = ftp_nlist($ftp, FtpTestConfig::targetDir());
        $this->assertContains(FtpTestConfig::targetDir() . '/' . $filename, $files);

        ftp_delete($ftp, FtpTestConfig::targetDir() . '/' . $filename);
        ftp_close($ftp);
    }
}
