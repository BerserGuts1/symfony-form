<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use JakubOlkowiczRekrutacjaSmartiveapp\Storage\StorageFactory;
use JakubOlkowiczRekrutacjaSmartiveapp\Image\ImageResizerInterface;

#[AsCommand(name: "thumbnail:generate")]
class GenerateThumbnailCommand extends Command
{
	private string $storageType;
	private string $source;
	private string $filename;

	public function __construct(
		private readonly ImageResizerInterface $resizer,
		private readonly StorageFactory $storageFactory
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setDescription("Generates a thumbnail and saves it to FTP or locally.");
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->titleCommand($output);
		$this->storageQuestion($input, $output);
		$this->sourceQuestion($input, $output);
		$this->filenameQuestion($input, $output);

		try {
			$storage = $this->storageFactory->create($this->storageType);
			$binary = $this->resizer->resize($this->source);
			$storage->save($this->filename, $binary);
			$output->writeln("<info>Thumbnail generated and saved.</info>");
			return Command::SUCCESS;
		} catch (\Throwable $e) {
			$output->writeln("<error>Error: " . $e->getMessage() . "</error>");
			return Command::FAILURE;
		}
	}

	private function titleCommand($output)
	{
		$output->writeln([
			"<comment>Generator of thumbnail</comment>",
			"<comment>============</comment>",
			"",
		]);
		sleep(1);
	}

	private function filenameQuestion(InputInterface $input, OutputInterface $output): void
	{
		$helper = $this->getHelper("question");

		$question = new Question("<question>Please enter the final file name:</question> ");
		$question->setValidator(function ($answer): string {
			$filename = trim($answer);

			if (!is_string($filename) || $filename === "") {
				throw new \RuntimeException("The file name must not be empty.");
			}

			if (str_contains($filename, "/") || str_contains($filename, "\\")) {
				throw new \RuntimeException("The file name must not contain paths or slashes.");
			}

			if (!preg_match("/\.(jpg|jpeg|png|webp)$/i", $filename)) {
				throw new \RuntimeException("File name must end in .jpg, .jpeg, .png or .webp");
			}

			return $filename;
		});
		$question->setMaxAttempts(3);

		$this->filename = $helper->ask($input, $output, $question);

		$this->handleExtensionMismatch($input, $output, $helper);

		$output->writeln(["You have chosen: " . $this->filename, ""]);
		sleep(1);
	}

	private function sourceQuestion($input, $output)
	{
		$helper = $this->getHelper("question");

		$question = new Question("<question>Specify the path to the source file:</question> ");
		$question->setValidator(function ($answer): string {
			$path = trim($answer);

			if (!is_string($path) || $path === "") {
				throw new \RuntimeException("Source is required and must be a string.");
			}

			if (!file_exists($path) || !is_file($path)) {
				throw new \RuntimeException("The source file $path does not exist or is not a file.");
			}

			if (!preg_match("/\.(jpg|jpeg|png|webp)$/i", $path)) {
				throw new \RuntimeException("File name must end in .jpg, .jpeg, .png or .webp");
			}

			return $answer;
		});
		$question->setMaxAttempts(3);

		$this->source = $helper->ask($input, $output, $question);

		$output->writeln(["You have chosen: " . $this->source, ""]);
		sleep(1);
	}

	private function storageQuestion($input, $output)
	{
		$helper = $this->getHelper("question");

		$storage = new ChoiceQuestion(
			"<question>Select type storage:</question> ",
			["ftp", "local"],
			0
		);
		$storage->setErrorMessage("Storage %s is invalid.");

		$this->storageType = $helper->ask($input, $output, $storage);

		$output->writeln(["You have just selected: " . $this->storageType, ""]);
		sleep(1);
	}

	private function handleExtensionMismatch(InputInterface $input, OutputInterface $output, HelperInterface $helper): void
	{
		$sourceExt = strtolower(pathinfo($this->source, PATHINFO_EXTENSION));
		$targetExt = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));

		if ($sourceExt !== $targetExt) {
			$output->writeln([
				"<comment>Note:</comment> You change the file extension from <info>{$sourceExt}</info> to <info>{$targetExt}</info>.",""
			]);

			$confirm = new ConfirmationQuestion(
				"<question>Are you sure you want to change the file format?</question>. (y/N): ",
				true
			);

			if (!$helper->ask($input, $output, $confirm)) {
				throw new \RuntimeException("The extension change was canceled by the user.");
			}
		}
	}
}
