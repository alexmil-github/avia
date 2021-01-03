<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
}
