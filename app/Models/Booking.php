<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_from',
        'flight_back',
        'date_from',
        'date_back',
        'code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
    ];

    public function passengers() {
        return $this->hasMany(Passenger::class);
    }

    public function list_passengers() { //получаем массив пассажиров для текущей брони
        $pass = Passenger::where ('booking_id', $this->id)->get();
        return $pass;
    }

    public function getCost() { //Подсчет общей суммы брони с учетом кол-ва пассажиров
        $cost = Flight::find($this->flight_from)->cost; //Стоимость рейса туда

        if ($this->flight_back) {
            $cost = $cost + Flight::find($this->flight_back)->cost;
        }
        return $cost*($this->passengers()->count() );
    }

    public static function FreePlaces($flight_id, $date) { // Количество свободных мест на рейсе

        $booking = Booking::where([
            ['flight_from', $flight_id],
            ['date_from', $date],
        ])->orWhere([
            ['flight_back', $flight_id],
            ['date_back', $date],
        ])->get();

        $sum = 0;
        foreach ($booking as $value){
            $sum = $sum + $value->passengers()->count();
        }
        return 103-$sum;
    }
}
