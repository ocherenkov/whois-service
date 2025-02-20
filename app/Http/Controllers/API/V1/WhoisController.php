<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\LookupRequest;
use App\Http\Resources\API\V1\WhoisResource;
use App\Services\API\WhoisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class WhoisController extends Controller
{

    public function lookup(LookupRequest $request, WhoisService $whoisService): JsonResponse
    {
        try {
            $result = $whoisService->lookup($request->validated(['domain']));
            return response()->json(new WhoisResource($result));
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
