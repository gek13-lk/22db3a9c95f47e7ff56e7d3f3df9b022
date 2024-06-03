<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;

class DataService
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function initializeDb(): void
    {
        $data = file_get_contents(__DIR__.'/mocks/generatedData.json', true);
        $data = json_decode($data, true);
        /*foreach ($data['norms'] as $competenciesData) {
            $competencies = new Competencies();
            $competencies
                ->setNorms($competenciesData['Норма в смену'])
                ->setModality($competenciesData['Модальность'])
                ->setType($competenciesData['Вид исследования']);

            $this->entityManager->persist($competencies);
        }

        $this->entityManager->flush();*/

        foreach ($data['doctors'] as $doctorData) {
            $doctor = new Doctor();
            $doctor->setMiddlename($doctorData['ID']);

            $this->entityManager->persist($doctor);

            foreach ($doctorData["Компетенции"]["Модальности"] as $modality) {
                $competencies = $this->entityManager->getRepository(Competencies::class)->findOneByTypeOrModality($modality);
                $doctor->addSpeciality($competencies);
            }

            foreach ($doctorData["Компетенции"]["Виды исследований"] as $type) {
                $competencies = $this->entityManager->getRepository(Competencies::class)->findOneByTypeOrModality(type: $type);
                $doctor->addSpeciality($competencies);
            }
        }

        $this->entityManager->flush();
    }

    public function generateInputData(): void
    {
        set_time_limit(600);
        //TODO: Перенести в БД входные данные
        $start_date = new \DateTime("2024-05-17");
        $end_date = new \DateTime("2024-05-24");
        $interval = new \DateInterval('P1W');
        $period = new \DatePeriod($start_date, $interval, $end_date);

        // Модальности и виды исследований
        $modalities = ['X-ray', 'CT', 'MRI', 'US', 'PET'];
        $study_types = ['Ортопедические', 'Неврологические', 'Абдоминальные', 'Кардиологические', 'Грудные', 'Другие'];

        $studiesByWeek = [];
        foreach ($period as $date) {
            foreach ($modalities as $modality) {
                foreach ($study_types as $study_type) {
                    $num_studies = rand(0, 100);
                    $studiesByWeek[] = [
                        'Дата' => $date,
                        'Модальность' => $modality,
                        'Вид исследования' => $study_type,
                        'Количество исследований' => $num_studies
                    ];
                }
            }
        }

        $studiesByDay = [];

        foreach ($studiesByWeek as $studies) {
            for ($i=1; $i <= $studies['Количество исследований']; $i++) {
                $studiesByDay[] = [
                    'Дата' => $this->getRandomDateInPeriod($studies['Дата'], $studies['Дата']->modify('+7 days')),
                    'Модальность' => $studies['Модальность'],
                    'Вид исследования' => $studies['Вид исследования'],
                ];
            }
        }

        // Нормы количества описанных исследований на одного врача в смену
        /*$norms = [
            ['Модальность' => 'X-ray', 'Вид исследования' => 'Ортопедические', 'Норма в смену' => 40],
            ['Модальность' => 'X-ray', 'Вид исследования' => 'Неврологические', 'Норма в смену' => 30],
            ['Модальность' => 'CT', 'Вид исследования' => 'Абдоминальные', 'Норма в смену' => 20],
            ['Модальность' => 'MRI', 'Вид исследования' => 'Кардиологические', 'Норма в смену' => 15],
            ['Модальность' => 'US', 'Вид исследования' => 'Грудные', 'Норма в смену' => 25],
            ['Модальность' => 'PET', 'Вид исследования' => 'Другие', 'Норма в смену' => 10],
            // Добавьте остальные комбинации
        ];*/

        // Список врачей с описанием их компетенций
        /*$doctors = [
            ['ID' => 'Врач 1', 'Компетенции' => ['Модальности' => ['X-ray', 'CT'], 'Виды исследований' => ['Ортопедические', 'Абдоминальные']]],
            ['ID' => 'Врач 2', 'Компетенции' => ['Модальности' => ['MRI', 'US'], 'Виды исследований' => ['Неврологические', 'Кардиологические']]],
            ['ID' => 'Врач 3', 'Компетенции' => ['Модальности' => ['PET'], 'Виды исследований' => ['Грудные', 'Другие']]],
            // Добавьте остальных врачей
        ];*/

        // Сохранение данных
        file_put_contents(
            __DIR__.'/mocks/generatedData.json',
            [
                'Количество исследований' => json_encode($studiesByDay, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                //'Нормы' => json_encode($norms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                //'Врачи' => json_encode($doctors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ]);
    }

    private function getRandomDateInPeriod(\DateTime $firstDate, \DateTime $secondDate): string
    {
        if ($firstDate < $secondDate) {
            return date('Y-m-d', mt_rand($firstDate->getTimestamp(), $secondDate->getTimestamp()));
        }

        return date('Y-m-d', mt_rand($secondDate->getTimestamp(), $firstDate->getTimestamp()));
    }
}