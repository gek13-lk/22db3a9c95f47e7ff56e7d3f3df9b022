<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607143824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE week_studies ADD start_of_week DATE DEFAULT NULL');

        $this->setStartOfWeek();
        $this->updateCurrentSchedules();
        $this->generateSchedules();
    }

    private function generateSchedules(): void
    {
        $data = [
            ['type' => 'Сутки через трое', 'hours_per_shift' => 24, 'shift_per_cycle' => 1, 'days_off' => 3],
            ['type' => 'Два выходных', 'hours_per_shift' => 8, 'shift_per_cycle' => 5, 'days_off' => 2],
            ['type' => 'Ночные смены', 'hours_per_shift' => 12, 'shift_per_cycle' => 4, 'days_off' => 3],
            ['type' => 'Дневные смены', 'hours_per_shift' => 8, 'shift_per_cycle' => 5, 'days_off' => 2],
            ['type' => 'День-ночь', 'hours_per_shift' => 12, 'shift_per_cycle' => 2, 'days_off' => 2],
        ];

        for ($i = 6; $i <= 313; $i++) {
            $schedule = $data[array_rand($data)];

            $this->addSql(
                'INSERT INTO public.doctor_work_schedules 
    (type, hours_per_shift, shift_per_cycle, days_off, doctor_id) 
VALUES (?, ?, ?, ?, ?)',
                [$schedule['type'], $schedule['hours_per_shift'], $schedule['shift_per_cycle'], $schedule['days_off'], $i]
            );
        }
    }

    private function setStartOfWeek(): void
    {
        $data = [
            ['date' => '2024-01-01', 'id' => 1],
            ['date' => '2024-01-01', 'id' => 2],
            ['date' => '2024-01-01', 'id' => 3],
            ['date' => '2024-01-01', 'id' => 4],
            ['date' => '2024-01-01', 'id' => 5],
            ['date' => '2024-01-01', 'id' => 6],
            ['date' => '2024-01-01', 'id' => 7],
            ['date' => '2024-01-01', 'id' => 8],
            ['date' => '2024-01-01', 'id' => 9],
            ['date' => '2024-01-01', 'id' => 10],
        ];

        foreach ($data as $startOfWeek) {
            $this->addSql(
                'UPDATE public.week_studies SET start_of_week = ? WHERE id = ?',
                [$startOfWeek['date'], $startOfWeek['id']]
            );
        }
    }

    private function updateCurrentSchedules(): void
    {
        $this->addSql('UPDATE public.doctor_work_schedules SET doctor_id = 3 WHERE id = 3');
        $this->addSql('UPDATE public.doctor_work_schedules SET doctor_id = 5 WHERE id = 5');
        $this->addSql('UPDATE public.doctor_work_schedules SET doctor_id = 2 WHERE id = 2');
        $this->addSql('UPDATE public.doctor_work_schedules SET doctor_id = 1 WHERE id = 1');
        $this->addSql('UPDATE public.doctor_work_schedules SET doctor_id = 4 WHERE id = 4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE week_studies DROP start_of_week');
    }
}
