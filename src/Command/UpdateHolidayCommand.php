<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Calendar;
use App\Enum\Holiday;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:holiday:update',
    description: 'Обновить данные о праздничных днях',
)]
final class UpdateHolidayCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Старт обновления данных');

        try {
            foreach ($this->em->getRepository(Calendar::class)->findAll() as $item) {
                $foundHoliday = false;

                foreach (Holiday::cases() as $holiday) {
                    if ($holiday->value === $item->getDate()->format('m-d')) {
                        $item->setHolidayName($holiday->label());
                        $foundHoliday = true;

                        break;
                    }
                }

                if (!$foundHoliday && $item->getHolidayName()) {
                    $item->setHolidayName(null);
                }

                $this->em->persist($item);
            }

            $this->em->flush();
            $io->success('Данные о праздничных днях успешно обновлены');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Ошибка обновления праздничных дней'.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
