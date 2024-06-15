<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Service\MLService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:models:training',
    description: 'Обучение моделей прогнозирования',
)]
final class ModelTrainingCommand extends Command
{
    public function __construct(
        private MLService $service,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Старт обучения моделей прогнозирования');

        try {
            $dateStart = new \DateTime();
            /** @var User|null $systemUser */
            $systemUser = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
            if (!$systemUser) {
                throw new \Exception('Не найден системный пользователь');
            }

            $io->info('Добавляем лог о событии');
            $log = $this->service->createLog($systemUser);
            $log->setIsSuccess();
            $this->em->flush();

            $io->info('Запускаем скрипт. Логи обучения: docker/django/model_training.log');
            $this->service->execute();

            $difference = $dateStart->diff(new \DateTime());
            $totalMinutes = $difference->days * 24 * 60 + $difference->h * 60 + $difference->i;
            $io->success('Модели прогнозирования успешно обучены за '. $totalMinutes .' минут');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $text = sprintf(
                'Ошибка обучения моделей прогнозирования: %s [ Логи ] - docker/django/model_training.log',
                $e->getMessage()
            );

            $io->error($text);

            return Command::FAILURE;
        }
    }
}
