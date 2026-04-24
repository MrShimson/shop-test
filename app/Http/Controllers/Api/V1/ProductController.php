<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListProductsRequest;
use App\Http\Resources\V1\ProductResource;
use App\UseCases\ListProductsUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(ListProductsRequest $request, ListProductsUseCase $useCase): AnonymousResourceCollection
    {
        return ProductResource::collection($useCase->handle($request));
    }
}
