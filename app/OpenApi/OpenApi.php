<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="LoudIsland API",
 *   version="1.0.0",
 *   description="API documentation for LoudIsland endpoints"
 * )
 *
 * @OA\Server(
 *   url="/",
 *   description="Base server"
 * )
 *
 * // Define bearer token auth if needed later
 * // @OA\SecurityScheme(
 * //   securityScheme="bearerAuth",
 * //   type="http",
 * //   scheme="bearer",
 * //   bearerFormat="JWT"
 * // )
 */
class OpenApi
{
    // This class is intentionally empty. It only holds global OpenAPI annotations.
}
