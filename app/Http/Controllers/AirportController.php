<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    public function search()
    {
        $query = $_GET['query'];

        $items = Airport::where('name', 'like', '%' . $query . '%')->orWhere('iata', 'like', '%' . $query . '%')->orWhere('city', 'like', '%' . $query . '%')->get();

        return response()->json([
            'data' => [
                'items' =>  $items,
            ]
        ]);
    }
}
