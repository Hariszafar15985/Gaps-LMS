<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Filter;

class FilterController extends Controller
{
    public function getByCategoryId($categoryId)
    {
        $filters = Filter::select('*')
            ->where('category_id', $categoryId)
            ->with([
                'options'  => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
            ->get();

        return response()->json([
            'filters' => $filters,
        ], 200);
    }
}
