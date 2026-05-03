<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
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
            ['name' => 'Мамадыш', 'lat' => 55.7149, 'lng' => 51.4070, 'population' => 15752],
            ['name' => 'Мензелинск', 'lat' => 55.7271, 'lng' => 53.1026, 'population' => 16948],
        ];

        City::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', ['агрыз'])
            ->orWhereRaw('LOWER(TRIM(name)) = ?', ['agryz'])
            ->delete();

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

        Movie::query()->delete();

        $movieTemplates = [
            ['title' => 'Дорога света', 'genre' => 'Драма', 'duration' => 112, 'age_rating' => '12', 'poster' => 'images/film1.jpg'],
            ['title' => 'Тайна фургона', 'genre' => 'Приключения', 'duration' => 98, 'age_rating' => '6', 'poster' => 'images/film2.jpg'],
            ['title' => 'Ночное небо', 'genre' => 'Фантастика', 'duration' => 124, 'age_rating' => '16', 'poster' => 'images/film3.jpg'],
            ['title' => 'Городской ритм', 'genre' => 'Комедия', 'duration' => 106, 'age_rating' => '12', 'poster' => 'images/film4.jpg'],
            ['title' => 'Перед рассветом', 'genre' => 'Триллер', 'duration' => 118, 'age_rating' => '18', 'poster' => 'images/film5.jpg'],
        ];

        $cities = City::query()->take(5)->get();
        foreach ($movieTemplates as $idx => $movieData) {
            $city = $cities[$idx % max(1, $cities->count())] ?? null;
            Movie::create([
                ...$movieData,
                'description' => 'Фильм для выездного кинотеатра с атмосферой большого экрана и живого общения.',
                'city_id' => $city?->id,
                'venue' => 'Центральная площадь',
                'show_time' => now()->addDays($idx + 1)->setTime(20, 0),
                'venue_capacity' => 150 + ($idx * 10),
                'expected_attendees' => 90 + ($idx * 8),
            ]);
        }
    }
}
