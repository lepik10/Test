<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.token');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page') ?? 15;
        $currencies = Currency::paginate($perPage)->appends([
            'per_page' => $perPage
        ]);
        return CurrencyResource::collection($currencies);
    }

    public function show($id)
    {
        $currency = Currency::where('id', $id);
        if ($currency->count() > 0) {
            return new CurrencyResource($currency->first());
        } else {
            return response()->json('This currency has not found!', 401);
        }

    }
}
