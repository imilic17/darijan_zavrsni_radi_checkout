<?php

// app/Http/Controllers/PostCodeController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostCode;

class PostCodeController extends Controller
{
    public function lookup(Request $request)
    {
        $city = trim((string) $request->query('city', ''));
        $country = $request->query('country', 'HR'); // default HR

        if ($city === '') {
            return response()->json(['postal_code' => null]);
        }

        $row = PostCode::query()
            ->whereRaw('LOWER(city) = ?', [mb_strtolower($city)])
            ->where(function ($q) use ($country) {
                $q->whereNull('country')->orWhere('country', $country);
            })
            ->first();

        return response()->json([
            'postal_code' => $row?->postal_code,
        ]);
    }
}
