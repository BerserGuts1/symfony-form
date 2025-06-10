<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Tests\Config;

final class FtpTestConfig
{
    public static function host(): string
    {
        return $_ENV['FTP_HOST'] ?? throw new \RuntimeException('Missing FTP_HOST');
    }

    public static function user(): string
    {
        return $_ENV['FTP_USER'] ?? throw new \RuntimeException('Missing FTP_USER');
    }

    public static function password(): string
    {
        return $_ENV['FTP_PASSWORD'] ?? throw new \RuntimeException('Missing FTP_PASSWORD');
    }

    public static function targetDir(): string
    {
        return $_ENV['FTP_TARGET_DIR'] ?? throw new \RuntimeException('Missing FTP_DIR');
    }
}
