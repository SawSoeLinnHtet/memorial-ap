<?php

namespace App;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function createdResponse(
        mixed $data = null,
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $code = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
