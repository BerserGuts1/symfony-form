<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Tests\Storage;

use PHPUnit\Framework\TestCase;
use JakubOlkowiczRekrutacjaSmartiveapp\Storage\LocalStorage;

class LocalStorageTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/local_storage_test';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }

    public function testSavesFile(): void
    {
        $storage = new LocalStorage($this->tempDir);
        $storage->save('test.jpg', 'abc123');

        $this->assertFileExists($this->tempDir . '/test.jpg');
        $this->assertSame('abc123', file_get_contents($this->tempDir . '/test.jpg'));
    }

    public function testRenamesIfFileExists(): void
    {
        file_put_contents($this->tempDir . '/test.jpg', 'original');

        $storage = new LocalStorage($this->tempDir);
        $storage->save('test.jpg', 'new');

        $this->assertFileExists($this->tempDir . '/test-1.jpg');
        $this->assertSame('new', file_get_contents($this->tempDir . '/test-1.jpg'));
    }
}
