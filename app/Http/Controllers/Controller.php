<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation reussie',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data ?? $this->emptyObject(),
            'message' => $message,
        ], $status);
    }

    protected function errorResponse(
        mixed $errors = null,
        string $message = 'Erreur',
        int $status = 422
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'errors' => $errors ?? $this->emptyObject(),
            'message' => $message,
        ], $status);
    }

    protected function emptyObject(): object
    {
        return (object) [];
    }
}
