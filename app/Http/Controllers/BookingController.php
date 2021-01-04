<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function booking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_from' => 'required',
            'flight_from.id' => 'required',
            'flight_from.date' => 'required|date_format:Y-m-d',
            'flight_back.id' => 'required',
            'flight_back.date' => 'required|date_format:Y-m-d',
            'passengers' => 'required|array|between:1,8',
            'passengers.*.first_name' => 'required',
            'passengers.*.last_name' => 'required',
            'passengers.*.birth_date' => 'required|date_format:Y-m-d',
            'passengers.*.document_number' => 'required|numeric|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $bookingData = [];

        $bookingData ['flight_from'] = $request->flight_from['id'];
        $bookingData ['date_from'] = $request->flight_from['date'];;
        $bookingData ['code'] = Str::upper(Str::random(5));

        if ($request->has('flight_back')) {
            $bookingData ['flight_back'] = $request->flight_back['id'];
            $bookingData ['date_back'] = $request->flight_back['date'];
        }

        $booking = Booking::create($bookingData);

        $booking->passengers()->createMany($request->passengers);

        return response()->json([
            'data' => [
                'code' => $booking->code,
            ]
        ], 201);

    }

    public function info(Booking $booking)
    {

        $flight_to = Flight::find($booking->flight_from); //Объект
        $flight_back = Flight::find($booking->flight_back); //Объект


        return response()->json([
            'data' => [
                'code' => $booking->code,
                'cost' => $booking->getCost(),
                'flhgts' => [
                    [
                        'flight_id' => $flight_to->id,
                        'flight_code' => $flight_to->flight_code,
                        'from' => [
                            'city' => Airport::find($flight_to->from_id)->city,
                            'airport' => Airport::find($flight_to->from_id)->name,
                            'iata' => Airport::find($flight_to->from_id)->iata,
                            'date' => $booking->date_from,
                            'time' => Carbon::parse($flight_to->time_from)->format('H:i'),
                        ],
                        'to' => [
                            'city' => Airport::find($flight_to->to_id)->city,
                            'airport' => Airport::find($flight_to->to_id)->name,
                            'iata' => Airport::find($flight_to->to_id)->iata,
                            'date' => $booking->date_from,
                            'time' => Carbon::parse($flight_to->time_to)->format('H:i'),
                        ],
                        'cost' => $flight_to->cost,
                        'availability' => Booking::FreePlaces($flight_to->id, $booking->date_from),
                    ],
                    [
                        'flight_id' => $flight_back->id,
                        'flight_code' => $flight_back->flight_code,
                        'from' => [
                            'city' => Airport::find($flight_back->from_id)->city,
                            'airport' => Airport::find($flight_back->from_id)->name,
                            'iata' => Airport::find($flight_back->from_id)->iata,
                            'date' => $booking->date_back,
                            'time' => Carbon::parse($flight_back->time_from)->format('H:i'),
                        ],
                        'to' => [
                            'city' => Airport::find($flight_back->to_id)->city,
                            'airport' => Airport::find($flight_back->to_id)->name,
                            'iata' => Airport::find($flight_back->to_id)->iata,
                            'date' => $booking->date_back,
                            'time' => Carbon::parse($flight_back->time_to)->format('H:i'),
                        ],
                        'cost' => $flight_back->cost,
                        'availability' => Booking::FreePlaces($flight_back->id, $booking->date_back),
                    ],
                ],
                'passengers' => $booking->list_passengers(),
            ],

        ], 200);
    }
}
