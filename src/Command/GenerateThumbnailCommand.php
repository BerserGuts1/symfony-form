<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use JakubOlkowiczRekrutacjaSmartiveapp\Storage\StorageFactory;
use JakubOlkowiczRekrutacjaSmartiveapp\Image\ImageResizerInterface;

#[AsCommand(name: "thumbnail:generate", description: "Generates a thumbnail and saves it to FTP or locally.")]
class GenerateThumbnailCommand extends Command
{
	public function __construct(
		private readonly ImageResizerInterface $resizer,
		private readonly StorageFactory $storageFactory
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addArgument("source", InputArgument::REQUIRED, "Path to the source image file")
			->addArgument("filename", InputArgument::REQUIRED, "Target filename with extension")
			->addArgument("storage", InputArgument::REQUIRED, "Storage type: ftp or local");
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$source = $input->getArgument("source");
		$filename = $input->getArgument("filename");
		$storageType = $input->getArgument("storage");

		try {
			$this->validateArguments($source, $filename, $storageType, $output);

			$storage = $this->storageFactory->create($storageType);
			$binary = $this->resizer->resize($source);
			$storage->save($filename, $binary);

			$output->writeln("<info>Thumbnail generated and saved successfully.</info>");
			return Command::SUCCESS;
		} catch (\Throwable $e) {
			$output->writeln("<error>Error: " . $e->getMessage() . "</error>");
			return Command::FAILURE;
		}
	}

	private function validateArguments(string $source, string $filename, string $storage, OutputInterface $output): void
	{
		if (!file_exists($source) || !is_file($source)) {
			throw new \InvalidArgumentException("Source file does not exist: {$source}");
		}

		if (!preg_match("/\.(jpg|jpeg|png|webp)$/i", $source)) {
			throw new \InvalidArgumentException("Source file must have a valid image extension (.jpg, .jpeg, .png, .webp)");
		}

		if (str_contains($filename, "/") || str_contains($filename, "\\")) {
			throw new \InvalidArgumentException("Filename must not contain slashes or paths.");
		}

		if (!preg_match("/\.(jpg|jpeg|png|webp)$/i", $filename)) {
			throw new \InvalidArgumentException("Filename must end with .jpg, .jpeg, .png or .webp");
		}

		if (!in_array($storage, ["ftp", "local"], true)) {
			throw new \InvalidArgumentException("Storage must be one of: ftp, local");
		}

		$this->warnIfExtensionMismatch($source, $filename);
	}

	private function warnIfExtensionMismatch(string $source, string $filename): void
	{
		$sourceExt = strtolower(pathinfo($source, PATHINFO_EXTENSION));
		$targetExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if ($sourceExt !== $targetExt) {
			throw new \InvalidArgumentException(
				"Source file has extension .$sourceExt, but target filename ends with .$targetExt — extension must match."
			);
		}
	}
}

