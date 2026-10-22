<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Item::class);

        $items = Item::with('category')
            ->when($request->query('search'), function ($query, $search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return ItemResource::collection($items);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $this->authorize('create', Item::class);

        $item = Item::create($request->validated())->load('category');

        return (new ItemResource($item))->response()->setStatusCode(201);
    }

    public function show(Item $item): ItemResource
    {
        $this->authorize('view', $item);

        return new ItemResource($item->load('category'));
    }

    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $this->authorize('update', $item);

        $item->update($request->validated());

        return new ItemResource($item->load('category'));
    }

    public function destroy(Item $item): Response
    {
        $this->authorize('delete', $item);

        $item->delete();

        return response()->noContent();
    }
}