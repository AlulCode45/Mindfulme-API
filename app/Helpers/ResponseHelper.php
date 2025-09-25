<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{
    /**
     * Response sukses dengan data.
     */
    public static function success($data = null, string $message = "Request berhasil", int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data,
        ], $code);
    }

    /**
     * Response gagal umum.
     */
    public static function error(string $message = "Terjadi kesalahan", int $code = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "errors" => $errors,
        ], $code);
    }

    /**
     * Response khusus jika data tidak ditemukan.
     */
    public static function notFound(string $message = "Data tidak ditemukan"): JsonResponse
    {
        return self::error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Response untuk unauthorized.
     */
    public static function unauthorized(string $message = "Unauthorized"): JsonResponse
    {
        return self::error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Response untuk forbidden.
     */
    public static function forbidden(string $message = "Forbidden"): JsonResponse
    {
        return self::error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Response untuk validasi gagal.
     */
    public static function validation(Validator $validator, string $message = "Validasi gagal"): JsonResponse
    {
        return self::error(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $validator->errors()
        );
    }

    /**
     * Response custom jika ingin fleksibel.
     */
    public static function custom(array $payload, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json($payload, $code);
    }
}
