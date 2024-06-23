<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Api\model\DoctorModel;
use App\Controller\Api\model\TempDoctorScheduleModel;
use App\Controller\Api\model\TempScheduleModel;
use App\Controller\Api\model\TempScheduleStudiesModel;
use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Entity\TempScheduleWeekStudies;
use App\Entity\User;
use App\Http\ApiResponse;
use App\Messenger\Message\MLMessage;
use App\Modules\Algorithm\AlgorithmWeekService;
use App\Service\MLService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Расписания')]
#[Route('dashboard')]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Ошибка доступа',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_FAIL),
        new OA\Property(property: 'message', type: 'string', example: ApiResponse::MESSAGE_FORBIDDEN),
        new OA\Property(property: 'result', type: 'array', items: new OA\Items()),
    ])
)]
final class DashboardController extends AbstractApiController
{
    /**
     * Получение расписаний.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getDashboards',
        description: 'Получение списка расписаний',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, список расписаний',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: TempScheduleModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/list', methods: Request::METHOD_GET)]
    public function list(): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО

        /** @var TempSchedule[] $tempSchedules */
        $tempSchedules = $this->getEm()->getRepository(TempSchedule::class)->findAll();

        $result = [];

        foreach ($tempSchedules as $tempSchedule) {
            $model = new TempScheduleModel();
            $model->maxDoctorsCount = $tempSchedule->getDoctorsMaxCount();
            $model->id = $tempSchedule->getId();
            $model->isApproved = $tempSchedule->isApproved();
            $model->fitness = $tempSchedule->getFitness();
            $model->createdAt = $tempSchedule->getCreatedAt();

            $result[] = $model;
        }

        return $this->responseSuccess($result);
    }

    /**
     * Получение рассчитанных списков исследований по неделям.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getWeekStudiesDashboards',
        description: 'Получение списка исследований по неделям',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, список исследований',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: TempScheduleStudiesModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/list-week-studies', methods: Request::METHOD_GET)]
    public function listWeekStudies(int $tempScheduleId): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО

        /** @var TempScheduleWeekStudies[] $tempSchedules */
        $tempSchedules = $this->getEm()->getRepository(TempScheduleWeekStudies::class)->findBy([
            'tempSchedule' => $tempScheduleId
        ]);

        $result = [];

        foreach ($tempSchedules as $tempSchedule) {
            $model = new TempScheduleStudiesModel();
            $weekStudiesEntity = $tempSchedule->getWeekStudies();
            $model->weekNumber = $weekStudiesEntity->getWeekNumber();
            $model->id = $tempSchedule->getId();
            $model->modality = $weekStudiesEntity->getCompetency()->getModality();
            $model->startWeek = $weekStudiesEntity->getStartOfWeek();
            $model->studiesCount = $weekStudiesEntity->getCount();
            $model->year = $weekStudiesEntity->getYear();
            $model->empty = $tempSchedule->getEmpty();

            $result[] = $model;
        }

        return $this->responseSuccess($result);
    }

    /**
     * Получение расписания по врачу.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getDoctorSchedules',
        description: 'Получение расписания врача',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, расписание по врачу',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: TempDoctorScheduleModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/doctor-schedules', methods: Request::METHOD_GET)]
    public function doctorSchedules(int $doctorId): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО

        /** @var TempDoctorSchedule[] $tempSchedules */
        $tempSchedules = $this->getEm()->getRepository(TempDoctorSchedule::class)->findBy([
            'doctor' => $doctorId
        ]);

        $result = [];

        foreach ($tempSchedules as $tempSchedule) {
            $doctorEntity = $tempSchedule->getDoctor();
            $doctor = new DoctorModel();
            $doctor->id = $doctorEntity->getId();
            $doctor->competencies = $doctorEntity->getCompetency();
            $doctor->competenciesAddon = $doctorEntity->getAddonCompetencies();
            $doctor->fio = $doctorEntity->getFio();

            $doctorSchedule = new TempDoctorScheduleModel();
            $doctorSchedule->doctor = $doctor;
            $doctorSchedule->id = $tempSchedule->getId();
            $doctorSchedule->studiesCount = $tempSchedule->getStudyCount();
            $doctorSchedule->date = $tempSchedule->getDate();
            $doctorSchedule->coefficient = $tempSchedule->getCoefficient();
            $doctorSchedule->end = $tempSchedule->getWorkTimeEnd();
            $doctorSchedule->start = $tempSchedule->getWorkTimeStart();
            $doctorSchedule->hours = $tempSchedule->getWorkHours();
            $doctorSchedule->off = $tempSchedule->getOffMinutes();

            $result[] = $doctorSchedule;
        }

        return $this->responseSuccess($result);
    }

    /**
     * Редактирование расписания по врачу.
     *
     * @throws \Exception
     */
    #[OA\Put(
        operationId: 'editDoctorSchedule',
        description: 'Редактирование расписания врача',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, расписание по врачу отредактировано',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: TempDoctorScheduleModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/doctor-schedules', methods: Request::METHOD_PUT)]
    public function editDoctorSchedule(int $doctorScheduleId, int $workHours, int $offTime, \DateTime $startTime, \DateTime $endTime): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО
        /** @var TempDoctorSchedule $tempSchedule */
        $tempSchedule = $this->getEm()->getRepository(TempDoctorSchedule::class)->find($doctorScheduleId);

        $tempSchedule->setWorkTimeStart($startTime);
        $tempSchedule->setOffMinutes($offTime);
        $tempSchedule->setWorkHours($workHours);
        $tempSchedule->setWorkTimeEnd($endTime);
        $this->getEm()->flush();

        $doctorEntity = $tempSchedule->getDoctor();
        $doctor = new DoctorModel();
        $doctor->id = $doctorEntity->getId();
        $doctor->competencies = $doctorEntity->getCompetency();
        $doctor->competenciesAddon = $doctorEntity->getAddonCompetencies();
        $doctor->fio = $doctorEntity->getFio();

        $doctorSchedule = new TempDoctorScheduleModel();
        $doctorSchedule->doctor = $doctor;
        $doctorSchedule->id = $tempSchedule->getId();
        $doctorSchedule->studiesCount = $tempSchedule->getStudyCount();
        $doctorSchedule->date = $tempSchedule->getDate();
        $doctorSchedule->coefficient = $tempSchedule->getCoefficient();
        $doctorSchedule->end = $tempSchedule->getWorkTimeEnd();
        $doctorSchedule->start = $tempSchedule->getWorkTimeStart();
        $doctorSchedule->hours = $tempSchedule->getWorkHours();
        $doctorSchedule->off = $tempSchedule->getOffMinutes();

        return $this->responseSuccess($doctorSchedule);
    }

    /**
     * Запуск формирования расписания.
     *
     * @throws \Exception
     */
    #[OA\Post(
        operationId: 'runSchedule',
        description: 'Запуск формирования расписания',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, расписание',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: TempScheduleModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/run-schedule', methods: Request::METHOD_POST)]
    public function runSchedule(AlgorithmWeekService $algorithmWeekService, int $maxDoctors, int $countSchedules, \DateTime $startTime, \DateTime $endTime): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $results = $algorithmWeekService->run($startTime, $endTime, $countSchedules, $maxDoctors);

        //TODO: в ДТО
        $result = [];

        foreach ($results as $tempSchedule) {
            $model = new TempScheduleModel();
            $model->maxDoctorsCount = $tempSchedule->getDoctorsMaxCount();
            $model->id = $tempSchedule->getId();
            $model->isApproved = $tempSchedule->isApproved();
            $model->fitness = $tempSchedule->getFitness();
            $model->createdAt = $tempSchedule->getCreatedAt();

            $result[] = $model;
        }

        return $this->responseSuccess($result);
    }
}