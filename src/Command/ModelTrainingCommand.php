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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsCommand(
    name: 'app:models:training',
    description: 'Обучение моделей прогнозирования',
)]
final class ModelTrainingCommand extends Command
{
    public function __construct(
        private MLService $service,
        private TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            /** @var User|null $systemUser */
            $systemUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);
            if (!$systemUser) {
                throw new \Exception('Не найден системный пользователь');
            }

            $token = new UsernamePasswordToken($systemUser, 'main');
            $this->tokenStorage->setToken($token);

            $this->service->createLog();
            $this->service->execute();

            $io->success('Модели прогнозирования успешно обучены');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->success('Ошибка обучения моделей прогнозирования: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
