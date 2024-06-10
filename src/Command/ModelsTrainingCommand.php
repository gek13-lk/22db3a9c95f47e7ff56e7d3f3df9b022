<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\WeekStudies;
use App\Enum\StudyType;
use App\Repository\WeekStudiesRepository;
use App\Service\MLService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ModelsTrainingCommand extends Command
{
    protected static $defaultName = 'app:train-models';

    public function __construct(private MLService $service)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Обучение моделей прогнозирования');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->service->execute();
            $data = $this->getData();

            $command = $this->trainModels($data['data']);
            exec($command, $outputLines, $returnVar);

            if ($returnVar === 1) {
                $io->success('[ Python ] Модели успешно обучены');

                $this->markData($data['ids']);

                $io->success('[ Symfony ] Используемые новые данные помечены устаревшими');

                return Command::SUCCESS;
            } else {
                $io->error('[ Python ] Ошибка обучения модели');
                foreach ($outputLines as $line) {
                    $io->writeln($line);
                }

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('[ Symfony ] Ошибка попытки обучения моделей: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    private function getData(): array
    {
        /** @var WeekStudiesRepository $repository */
        $repository = $this->em->getRepository(WeekStudies::class);

        $result = [];
        $ids = [];
        foreach ($repository->getStructuredData() as $structuredData) {
            $result[] = $this->prepareModelFromData($structuredData);
            if ($structuredData['is_new']) {
                $ids = array_merge($ids, $this->getIds($structuredData));
            }
        }

        return [
            'data' => $result,
            'ids' => $ids,
        ];
    }

    private function prepareModelFromData(array $data): array
    {
        $model = [
            'Year' => $data['year'],
            'Week' => $data['week_number'],
        ];

        foreach (StudyType::cases() as $studyType) {
            $model['count_'.$studyType->value] = $this->getStudiesCount($data[$studyType->value]);
        }

        return $model;
    }

    private function getStudiesCount(string $json): int
    {
        $count = json_decode($json, true);

        return $count ? $count[0]['count'] : 0;
    }

    private function getIds(array $data): array
    {
        $ids = [];
        foreach ($data as $item) {
            if (is_string($item) && ($info = json_decode($item, true))) {
                $ids[] = $info[0]['id'];
            }
        }

        return $ids;
    }

    private function markData(array $data): void
    {
        /** @var WeekStudiesRepository $repository */
        $repository = $this->em->getRepository(WeekStudies::class);
        $newData = $repository->findBy(['id' => $data]);

        /** @var WeekStudies $weekStudies */
        foreach ($newData as $weekStudies) {
            $weekStudies->setIsNotNew();
        }

        $this->em->flush();
    }

    private function trainModels(array $data): string
    {
        $dataJson = json_encode($data);

        return escapeshellcmd("python3 train_model.py '$dataJson'");
    }
}
