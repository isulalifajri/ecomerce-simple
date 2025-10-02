<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Tes E-commerce Simple API",
 *     description="Dokumentasi API untuk tes e-commerce sederhana dengan Laravel dan L5-Swagger",
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:9090",
 *     description="Localhost API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Masukkan token hasil login: Bearer {token}"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
