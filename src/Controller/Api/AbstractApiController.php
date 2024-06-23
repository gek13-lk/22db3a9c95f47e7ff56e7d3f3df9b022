<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @method User|null getUser()
 */
abstract class AbstractApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'validator' => ValidatorInterface::class,
        ]);
    }

    final protected function responseSuccess($data = [], ?string $message = null, int $status = Response::HTTP_OK, ?array $groups = null): JsonResponse
    {
        return $this->response(new ApiResponse($data, $message, ApiResponse::STATUS_SUCCESS), $status, $groups);
    }

    final protected function responseSuccessItems($data = [], ?string $message = null, int $status = Response::HTTP_OK, ?array $groups = null): JsonResponse
    {
        $result = [
            'items' => $data,
            'count' => count($data),
        ];

        return $this->response(new ApiResponse($result, $message, ApiResponse::STATUS_SUCCESS), $status, $groups);
    }

    final protected function responseFail($data = [], ?string $message = null, int $status = Response::HTTP_BAD_REQUEST, ?array $groups = null): JsonResponse
    {
        return $this->response(new ApiResponse($data, $message, ApiResponse::STATUS_FAIL), $status, $groups);
    }

    final protected function responseFailExtended($data, ?string $message = null, int $status = Response::HTTP_BAD_REQUEST, ?array $groups = null): JsonResponse
    {
        $result = [
            'errors' => $data,
        ];

        return $this->response(new ApiResponse($result, $message, ApiResponse::STATUS_FAIL), $status, $groups);
    }

    final protected function responseFailNotFound($data = [], ?string $message = null, ?array $groups = null): JsonResponse
    {
        $apiResponse = new ApiResponse($data, $message, ApiResponse::STATUS_FAIL);

        return $this->response($apiResponse, Response::HTTP_NOT_FOUND, $groups);
    }

    final protected function responseError($data, ?string $message = null, int $status = Response::HTTP_INTERNAL_SERVER_ERROR, ?array $groups = null): JsonResponse
    {
        return $this->response(new ApiResponse($data, $message, ApiResponse::STATUS_FAIL), $status, $groups);
    }

    final protected function responseErrorExtended($data, ?string $message = null, int $status = Response::HTTP_INTERNAL_SERVER_ERROR, ?array $groups = null): JsonResponse
    {
        $result = [
            'errors' => $data,
        ];

        return $this->response(new ApiResponse($result, $message, ApiResponse::STATUS_FAIL), $status, $groups);
    }

    final protected function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    private function response(ApiResponse $data, int $status = Response::HTTP_OK, ?array $groups = null): JsonResponse
    {
        return $this->json($data, $status, [], [
            'groups' => $groups,
            'datetime_format' => 'U',
            'datetime_timezone' => 'UTC',
        ]);
    }

    protected function accessDeniedException(string $message = 'Access Denied.'): void
    {
        throw $this->createAccessDeniedException($message);
    }
}