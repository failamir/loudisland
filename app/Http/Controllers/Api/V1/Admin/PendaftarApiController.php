<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StorePendaftarRequest;
use App\Http\Requests\UpdatePendaftarRequest;
use App\Http\Resources\Admin\PendaftarResource;
use App\Models\Pendaftar;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

class PendaftarApiController extends Controller
{
    use MediaUploadingTrait;

    /**
     * @OA\Tag(
     *     name="Pendaftar",
     *     description="Endpoints for managing pendaftar"
     * )
     *
     * @OA\Info(
     *     version="1.0.0",
     *     title="LoudIsland API",
     *     description="API documentation for LoudIsland endpoints"
     * )
     */

    /**
     * List pendaftar with pagination
     *
     * @OA\Get(
     *     path="/api/v1/pendaftars",
     *     tags={"Pendaftar"},
     *     summary="Get paginated list of pendaftar",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function index()
    {
        // abort_if(Gate::denies('pendaftar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PendaftarResource(Pendaftar::with(['event'])->paginate(10));
    }

    /**
     * Create a new pendaftar
     *
     * @OA\Post(
     *     path="/api/v1/pendaftars",
     *     tags={"Pendaftar"},
     *     summary="Create pendaftar",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Pendaftar")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StorePendaftarRequest $request)
    {
        $pendaftar = Pendaftar::create($request->all());

        return (new PendaftarResource($pendaftar))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Get a single pendaftar by ID
     *
     * @OA\Get(
     *     path="/api/v1/pendaftars/{id}",
     *     tags={"Pendaftar"},
     *     summary="Get pendaftar",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Pendaftar $pendaftar)
    {
        // abort_if(Gate::denies('pendaftar_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PendaftarResource($pendaftar->load(['event']));
    }

    /**
     * Update a pendaftar
     *
     * @OA\Put(
     *     path="/api/v1/pendaftars/{id}",
     *     tags={"Pendaftar"},
     *     summary="Update pendaftar",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Pendaftar")
     *         )
     *     ),
     *     @OA\Response(response=202, description="Accepted"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdatePendaftarRequest $request, Pendaftar $pendaftar)
    {
        $pendaftar->update($request->all());

        return (new PendaftarResource($pendaftar))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Delete a pendaftar
     *
     * @OA\Delete(
     *     path="/api/v1/pendaftars/{id}",
     *     tags={"Pendaftar"},
     *     summary="Delete pendaftar",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Pendaftar $pendaftar)
    {
        abort_if(Gate::denies('pendaftar_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pendaftar->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
