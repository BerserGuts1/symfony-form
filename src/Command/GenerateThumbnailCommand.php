<?php

namespace JakubOlkowiczRekrutacjaSmartiveapp\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

use JakubOlkowiczRekrutacjaSmartiveapp\Storage\StorageFactory;
use JakubOlkowiczRekrutacjaSmartiveapp\Image\ImageResizerInterface;

#[AsCommand(name: 'thumbnail:generate')]
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
			->setDescription('Generuje miniaturkę i zapisuje ją na FTP lub lokalnie.')
			->addArgument('source', InputArgument::OPTIONAL, 'Ścieżka do pliku źródłowego')
			->addArgument('filename', InputArgument::OPTIONAL, 'Docelowa nazwa pliku miniatury')
			->addOption('storage', null, InputOption::VALUE_OPTIONAL, 'ftp|local', );
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->titleCommand($output);
		$this->storageQuestion($input, $output);
		$this->filenameQuestion($input, $output);
		$this->sourceQuestion($input, $output);

		try {
			$storage = $this->storageFactory->create($this->storageType);
			$binary = $this->resizer->resize($this->source);
			$storage->save($this->filename, $binary);
			$output->writeln('<info>Miniatura wygenerowana i zapisana.</info>');
			return Command::SUCCESS;
		} catch (\Throwable $e) {
			$output->writeln('<error>Błąd: ' . $e->getMessage() . '</error>');
			return Command::FAILURE;
		}
	}

	private function titleCommand($output)
	{
		$output->writeln([
			'<comment>Thumbnail Creator</comment>',
			'<comment>============</comment>',
			'',
		]);
		sleep(1);
	}

	private function filenameQuestion(InputInterface $input, OutputInterface $output): void
	{
		$helper = $this->getHelper('question');

		$question = new Question('<question>Please enter the filename:</question> ');
		$question->setValidator(function ($answer): string {
			$filename = trim($answer);

			if (!is_string($filename) || $filename === '') {
				throw new \RuntimeException('Nazwa pliku nie może być pusta.');
			}
		
			if (str_contains($filename, '/') || str_contains($filename, '\\')) {
				throw new \RuntimeException('Nazwa pliku nie może zawierać ścieżek ani ukośników.');
			}
		
			if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $filename)) {
				throw new \RuntimeException('Nazwa pliku musi kończyć się na .jpg, .jpeg, .png lub .webp');
			}
		
			return $filename;
		});
		$question->setMaxAttempts(3);

		$this->filename = $helper->ask($input, $output, $question);

		$output->writeln(['You have just selected: ' . $this->filename, '']);
		sleep(1);
	}

	private function sourceQuestion($input, $output)
	{
		$helper = $this->getHelper('question');

		$question = new Question('<question>Podaj ścieżkę do pliku źródłowego:</question> ');
		$question->setValidator(function ($answer): string {
			$path = trim($answer);

			if (!is_string($path) || $path === '') {
				throw new \RuntimeException('Source is required and must be a string.');
			}

			if (!file_exists($path) || !is_file($path)) {
				throw new \RuntimeException("Plik źródłowy '$path' nie istnieje lub nie jest plikiem.");
			}
		
			if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $path)) {
				throw new \RuntimeException('Nazwa pliku musi kończyć się na .jpg, .jpeg, .png lub .webp');
			}

			return $answer;
		});
		$question->setMaxAttempts(3);

		$this->source = $helper->ask($input, $output, $question);
		
		$output->writeln(['You have just selected: ' . $this->source, '']);
		sleep(1);
	}

	private function storageQuestion($input, $output)
	{
		$helper = $this->getHelper('question');

		$storage = new ChoiceQuestion(
			'<question>Wybierz typ storage:</question> ',
			['ftp', 'local'],
			0
		);
		$storage->setErrorMessage('Storage %s is invalid.');

		$this->storageType = $helper->ask($input, $output, $storage);

		$output->writeln(['You have just selected: ' . $this->storageType, '']);
		sleep(1);
	}
}
