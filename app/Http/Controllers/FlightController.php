<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlightController extends Controller
{

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|exists:airports,iata',
            'to' => 'required|exists:airports,iata',
            'date1' => 'required|date_format:Y-m-d',
            'date2' => 'date_format:Y-m-d',
            'passengers' => 'required|int|between:1,8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ]
            ], 422);
        }
        $airport_from = Airport::where('iata', $request->from)->first(); //Получаем объект - аэропорт вылета по известному iata
        $airport_to = Airport::where('iata', $request->to)->first(); //Получаем аэропорт - фэропорт прилета по известному iata

        $flight_to = Flight::where([           //Получаем массив рейсов туда
            ['from_id', $airport_from->id],
            ['to_id', $airport_to->id]
        ])->get();

        $flight_back = Flight::where([          //Получаем массив рейсов обратно
            ['from_id', $airport_to->id],
            ['to_id', $airport_from->id]
        ])->get();

        //Формируем новый массив для заполнения flight_to
        $data_flight_to = [];
        $k = 0;
        foreach ($flight_to as $val) {
            $data_flight_to[$k] = [
                'flight_id' => $val['id'],
                'flight_code' => $val['flight_code'],
                'from' => [
                    'city' => $airport_from->city,
                    'airport' => $airport_from->name,
                    'iata' => $airport_from->iata,
                    'date' => $request->date1,
                    'time' => Carbon::parse($val->time_from)->format('H:i'),
                ],
                'to' => [
                    'city' => $airport_to->city,
                    'airport' => $airport_to->name,
                    'iata' => $airport_to->iata,
                    'date' => $request->date1,
                    'time' => Carbon::parse($val->time_to)->format('H:i'),
                ],
                'cost' => $val['cost'],
                'availability' => Booking::FreePlaces($val['id'], $request->date1),
            ];
            $k++;
        }
        //Формируем новый массив для заполнения flight_back
        $data_flight_back = [];
        $k = 0;
        foreach ($flight_back as $val) {
            $data_flight_back[$k] = [
                'flight_id' => $val['id'],
                'flight_code' => $val['flight_code'],
                'from' => [
                    'city' => $airport_to->city,
                    'airport' => $airport_to->name,
                    'iata' => $airport_to->iata,
                    'date' => $request->date2,
                    'time' => Carbon::parse($val->time_to)->format('H:i'),
                ],
                'to' => [
                    'city' => $airport_from->city,
                    'airport' => $airport_from->name,
                    'iata' => $airport_from->iata,
                    'date' => $request->date2,
                    'time' => Carbon::parse($val->time_from)->format('H:i'),
                ],
                'cost' => $val['cost'],
                'availability' => Booking::FreePlaces($val['id'], $request->date2),
            ];
            $k++;
        }

        return response()->json([
            'data' => [
                'flight_to' => $data_flight_to,
                'flight_back' => $data_flight_back,
            ]
        ], 200);

    }

}


