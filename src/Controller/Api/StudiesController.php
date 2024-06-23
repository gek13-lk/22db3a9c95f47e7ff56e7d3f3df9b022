<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Api\model\StudiesModel;
use App\Entity\PredictedWeekStudies;
use App\Entity\User;
use App\Entity\WeekStudies;
use App\Http\ApiResponse;
use App\Messenger\Message\MLMessage;
use App\Service\MLService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Исследования')]
#[Route('studies')]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Ошибка доступа',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_FAIL),
        new OA\Property(property: 'message', type: 'string', example: ApiResponse::MESSAGE_FORBIDDEN),
        new OA\Property(property: 'result', type: 'array', items: new OA\Items()),
    ])
)]
final class StudiesController extends AbstractApiController
{
    /**
     * Получение списка исследований по неделям.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getStudies',
        description: 'Получение исследований',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, исследования',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: StudiesModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/list-studies', methods: Request::METHOD_GET)]
    public function studiesList(): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО
        /** @var WeekStudies[] $studies */
        $studies = $this->getEm()->getRepository(WeekStudies::class)->findAll();

        $res = [];

        foreach ($studies as $study) {
            $model = new StudiesModel();
            $model->id = $study->getId();
            $model->modality = $study->getCompetency()->getModality();
            $model->year = $study->getYear();
            $model->studiesCount = $study->getCount();
            $model->weekNumber = $study->getWeekNumber();
            $model->startWeek = $study->getStartOfWeek();

            $res[] = $model;
        }

        return $this->responseSuccess($res);
    }

    /**
     * Получение списка предсказанных исследований по неделям.
     *
     * @throws \Exception
     */
    #[OA\Get(
        operationId: 'getPredicatedStudies',
        description: 'Получение предсказанных исследований',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, исследования',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'result', ref: new Model(type: StudiesModel::class), type: 'object'),
                ])
            ),
        ]
    )]
    #[Route(path: '/list-predicated-studies', methods: Request::METHOD_GET)]
    public function predicatedStudiesList(): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        //TODO: в ДТО
        /** @var PredictedWeekStudies[] $studies */
        $studies = $this->getEm()->getRepository(PredictedWeekStudies::class)->findAll();

        $res = [];

        foreach ($studies as $study) {
            $model = new StudiesModel();
            $model->id = $study->getId();
            $model->modality = $study->getCompetency()->getModality();
            $model->year = $study->getYear();
            $model->studiesCount = $study->getCount();
            $model->weekNumber = $study->getWeekNumber();
            $model->startWeek = $study->getStartOfWeek();

            $res[] = $model;
        }

        return $this->responseSuccess($res);
    }

    /**
     * Запуск обучения предсказательной модели.
     *
     * @throws \Exception
     */
    #[OA\Post(
        operationId: 'runPredicate',
        description: 'Запуск обучения предсказательной модели',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успех, расписание',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'string', example: ApiResponse::STATUS_SUCCESS),
                    new OA\Property(property: 'message', type: 'string'),
                ])
            ),
        ]
    )]
    #[Route(path: '/run-predicate', methods: Request::METHOD_POST)]
    public function runPredicate(MLService $service, MessageBusInterface $bus): JsonResponse
    {
        if (!$this->isGranted('ROLE_API')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $logs = $service->createLog($user);
            $bus->dispatch(new MLMessage($logs->getId()));

            return $this->responseSuccess(message: 'start models training');
        } catch (\Exception $e) {
            return $this->responseFail(message: $e->getMessage(), status: 500);
        }
    }
}