<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $routeCities = [
            ['name' => 'Набережные Челны', 'lat' => 55.7436, 'lng' => 52.3958, 'population' => 548434],
            ['name' => 'Елабуга', 'lat' => 55.7567, 'lng' => 52.0544, 'population' => 74390],
            ['name' => 'Нижнекамск', 'lat' => 55.6361, 'lng' => 51.8245, 'population' => 241329],
            ['name' => 'Альметьевск', 'lat' => 54.9014, 'lng' => 52.2971, 'population' => 152580],
            ['name' => 'Лениногорск', 'lat' => 54.6020, 'lng' => 52.4609, 'population' => 60878],
            ['name' => 'Бугульма', 'lat' => 54.5364, 'lng' => 52.7895, 'population' => 81744],
            ['name' => 'Азнакаево', 'lat' => 54.8579, 'lng' => 53.0698, 'population' => 32926],
            ['name' => 'Заинск', 'lat' => 55.3207, 'lng' => 52.0669, 'population' => 41498],
            ['name' => 'Менделеевск', 'lat' => 55.8952, 'lng' => 52.3144, 'population' => 22442],
            ['name' => 'Мензелинск', 'lat' => 55.7271, 'lng' => 53.1026, 'population' => 16948],
        ];

        foreach ($routeCities as $city) {
            City::updateOrCreate(
                ['name' => $city['name']],
                [
                    'lat' => $city['lat'],
                    'lng' => $city['lng'],
                    'population' => $city['population'],
                ]
            );
        }
    }
}
