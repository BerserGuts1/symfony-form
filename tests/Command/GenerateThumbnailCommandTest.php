<?php 

namespace JakubOlkowiczRekrutacjaSmartiveapp\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use JakubOlkowiczRekrutacjaSmartiveapp\Command\GenerateThumbnailCommand;
use JakubOlkowiczRekrutacjaSmartiveapp\Image\ImageResizerInterface;
use JakubOlkowiczRekrutacjaSmartiveapp\Storage\StorageFactory;
use JakubOlkowiczRekrutacjaSmartiveapp\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class GenerateThumbnailCommandTest extends TestCase
{
    public function testSuccessfulExecution(): void
    {
        $resizer = $this->createMock(ImageResizerInterface::class);
        $resizer->expects($this->once())
            ->method('resize')
            ->willReturn('binary content');

        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('save')
            ->with('output.png', 'binary content');

        $factory = $this->createMock(StorageFactory::class);
        $factory->method('create')->willReturn($storage);

        $command = new GenerateThumbnailCommand($resizer, $factory);
        $tester = new CommandTester($command);

        $tester->execute([
            'source' => 'assets/sample.png',
            'filename' => 'output.png',
            'storage' => 'local',
        ]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('successfully', $tester->getDisplay());
    }
}
