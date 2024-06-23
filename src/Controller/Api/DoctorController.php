<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Api\model\DoctorModel;
use App\Controller\Api\model\TempDoctorScheduleModel;
use App\Controller\Api\model\TempScheduleModel;
use App\Controller\Api\model\TempScheduleStudiesModel;
use App\Entity\Doctor;
use App\Entity\OffDoctorDays;
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

#[OA\Tag(name: 'Врач')]
#[Route('doctor')]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Ошибка доступа',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_FAIL),
        new OA\Property(property: 'message', type: 'string', example: ApiResponse::MESSAGE_FORBIDDEN),
        new OA\Property(property: 'result', type: 'array', items: new OA\Items()),
    ])
)]
final class DoctorController extends AbstractApiController
{
    /**
     * Получение списка врачей.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getDoctors',
        description: 'Получение врачей',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, врачи',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: DoctorModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/list-doctor', methods: Request::METHOD_GET)]
    public function doctorsList(): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО
        /** @var Doctor[] $doctorsEntity */
        $doctorsEntity = $this->getEm()->getRepository(Doctor::class)->findAll();

        $res = [];

        foreach ($doctorsEntity as $doctorEntity) {
            $doctorModel = new DoctorModel();
            $doctorModel->id = $doctorEntity->getId();
            $doctorModel->competencies = $doctorEntity->getCompetency();
            $doctorModel->competenciesAddon = $doctorEntity->getAddonCompetencies();
            $doctorModel->fio = $doctorEntity->getFio();

            $res[] = $doctorModel;
        }

        return $this->responseSuccess($res);
    }

    /**
     * Получение расписания по врачу.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getDoctor',
        description: 'Получение врача',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, врач',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: DoctorModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/doctor', methods: Request::METHOD_GET)]
    public function doctor(int $doctorId): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО
        /** @var Doctor $doctorEntity */
        $doctorEntity = $this->getEm()->getRepository(Doctor::class)->find($doctorId);

        $doctorModel = new DoctorModel();
        $doctorModel->id = $doctorEntity->getId();
        $doctorModel->competencies = $doctorEntity->getCompetency();
        $doctorModel->competenciesAddon = $doctorEntity->getAddonCompetencies();
        $doctorModel->fio = $doctorEntity->getFio();

        return $this->responseSuccess($doctorModel);
    }


    /**
     * Создание врача.
     *
     * @throws \Exception
     */
    #[OA\Post(
        operationId: 'createDoctor',
        description: 'Создание врача',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, врач',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: DoctorModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/doctor', methods: Request::METHOD_POST)]
    public function doctorCreate(array $competencies, array $competenciesAddon, float $stavka): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }


        $doctorEntity = new Doctor();
        $doctorEntity->setCompetency($competencies);
        $doctorEntity->setAddonCompetencies($competenciesAddon);
        $doctorEntity->setStavka($stavka);
        $this->getEm()->persist($doctorEntity);
        $this->getEm()->flush();

        //TODO: в ДТО
        $doctorModel = new DoctorModel();
        $doctorModel->id = $doctorEntity->getId();
        $doctorModel->competencies = $doctorEntity->getCompetency();
        $doctorModel->competenciesAddon = $doctorEntity->getAddonCompetencies();
        $doctorModel->fio = $doctorEntity->getFio();

        return $this->responseSuccess($doctorModel);
    }

    /**
     * Удалить врача.
     *
     * @throws \Exception
     */
    #[OA\Delete(
        operationId: 'deleteDoctor',
        description: 'Удалить врача',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, удалено',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                ])
            ),
        ]
    )]
    #[Route(path: '/delete-doctor', methods: Request::METHOD_DELETE)]
    public function deleteDoctor(int $doctor): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        /** @var Doctor $doctorEntity */
        $doctorEntity = $this->getEm()->getRepository(Doctor::class)->find($doctor);

        $this->getEm()->remove($doctorEntity);
        $this->getEm()->flush();

        return $this->responseSuccess();
    }

    /**
     * Запросить выходной(-ые) по врачу.
     *
     * @throws \Exception
     */
    #[OA\Post(
        operationId: 'setOffDays',
        description: 'Запросить выходной(-ые) по врачу',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, запрос создан',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                ])
            ),
        ]
    )]
    #[Route(path: '/set-off-days', methods: Request::METHOD_POST)]
    public function setOffDays(int $doctorId, \DateTime $startTime, \DateTime $endTime, string $reason): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        /** @var Doctor $doctorEntity */
        $doctorEntity = $this->getEm()->getRepository(Doctor::class)->find($doctorId);

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startTime, $interval, $endTime);

        foreach ($period as $date) {
            $offDay = new OffDoctorDays();
            $offDay->setDoctor($doctorEntity);
            $offDay->setReason($reason);
            $offDay->setDate($date);
            $this->getEm()->persist($offDay);
        }

        $this->getEm()->flush();

        return $this->responseSuccess();
    }

    /**
     * Удалить выходной(-ые) по врачу.
     *
     * @throws \Exception
     */
    #[OA\Delete(
        operationId: 'deleteOffDays',
        description: 'Удалить выходной(-ые) по врачу',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, удалено',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                ])
            ),
        ]
    )]
    #[Route(path: '/delete-off-days', methods: Request::METHOD_DELETE)]
    public function deleteOffDays(int $offDayId): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        /** @var OffDoctorDays $doctorOffEntity */
        $doctorOffEntity = $this->getEm()->getRepository(OffDoctorDays::class)->find($offDayId);

        $this->getEm()->remove($doctorOffEntity);
        $this->getEm()->flush();

        return $this->responseSuccess();
    }
}