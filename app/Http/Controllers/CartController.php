<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Endpoint JSON untuk drawer keranjang. */
class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function show(): JsonResponse
    {
        return response()->json($this->cart->summary());
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate(['variant_id' => ['required', 'integer']]);
        return $this->run(fn () => $this->cart->add((int) $data['variant_id']));
    }

    public function change(Request $request): JsonResponse
    {
        $data = $request->validate(['variant_id' => ['required', 'integer'], 'delta' => ['required', 'integer', 'in:-1,1']]);
        return $this->run(fn () => $this->cart->change((int) $data['variant_id'], (int) $data['delta']));
    }

    private function run(callable $action): JsonResponse
    {
        try {
            $action();
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()] + $this->cart->summary(), 422);
        }
        return response()->json($this->cart->summary());
    }
}
