<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $routeCities = [
            ['name' => 'Набережные Челны', 'lat' => 55.7436, 'lng' => 52.3958, 'population' => 548434, 'route_order' => 1],
            ['name' => 'Менделеевск', 'lat' => 55.8952, 'lng' => 52.3144, 'population' => 22442, 'route_order' => 2],
            ['name' => 'Елабуга', 'lat' => 55.7567, 'lng' => 52.0544, 'population' => 74390, 'route_order' => 3],
            ['name' => 'Нижнекамск', 'lat' => 55.6361, 'lng' => 51.8245, 'population' => 241329, 'route_order' => 4],
            ['name' => 'Мамадыш', 'lat' => 55.7149, 'lng' => 51.4070, 'population' => 15752, 'route_order' => 5],
            ['name' => 'Заинск', 'lat' => 55.3207, 'lng' => 52.0669, 'population' => 41498, 'route_order' => 6],
            ['name' => 'Альметьевск', 'lat' => 54.9014, 'lng' => 52.2971, 'population' => 152580, 'route_order' => 7],
            ['name' => 'Лениногорск', 'lat' => 54.6020, 'lng' => 52.4609, 'population' => 60878, 'route_order' => 8],
            ['name' => 'Бугульма', 'lat' => 54.5364, 'lng' => 52.7895, 'population' => 81744, 'route_order' => 9],
            ['name' => 'Азнакаево', 'lat' => 54.8579, 'lng' => 53.0698, 'population' => 32926, 'route_order' => 10],
            ['name' => 'Мензелинск', 'lat' => 55.7271, 'lng' => 53.1026, 'population' => 16948, 'route_order' => 11],
        ];

        $cities = collect($routeCities)->mapWithKeys(function (array $city, int $index) {
            $model = City::updateOrCreate(
                ['name' => $city['name']],
                [
                    'lat' => $city['lat'],
                    'lng' => $city['lng'],
                    'population' => $city['population'],
                    'route_order' => $city['route_order'],
                ]
            );

            return [$city['name'] => $model];
        });

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор маршрута',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'city_id' => $cities['Набережные Челны']->id,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Демо зритель',
                'password' => Hash::make('password'),
                'role' => 'user',
                'city_id' => $cities['Альметьевск']->id,
            ]
        );

        $guest = User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Активный зритель',
                'password' => Hash::make('password'),
                'role' => 'user',
                'city_id' => $cities['Елабуга']->id,
            ]
        );

        $moviesData = [
            ['title' => 'Звёздный автобус', 'genre' => 'Приключения', 'duration' => 104, 'age_rating' => '6', 'poster' => 'images/film1.jpg', 'city' => 'Набережные Челны', 'venue' => 'Площадь Азатлык', 'show_time' => now()->addDays(1)->setTime(19, 30), 'venue_capacity' => 120, 'expected_attendees' => 96, 'description' => 'Семейная история о школьниках, которые строят мобильный планетарий и отправляются показывать кино по малым городам.'],
            ['title' => 'Лето на Каме', 'genre' => 'Комедия', 'duration' => 97, 'age_rating' => '12', 'poster' => 'images/film2.jpg', 'city' => 'Елабуга', 'venue' => 'Набережная Тоймы', 'show_time' => now()->addDays(2)->setTime(20, 0), 'venue_capacity' => 90, 'expected_attendees' => 74, 'description' => 'Добрая комедия о соседях, которые устраивают фестиваль дворового кино и неожиданно становятся героями всего района.'],
            ['title' => 'Тайна старой мельницы', 'genre' => 'Детектив', 'duration' => 112, 'age_rating' => '12', 'poster' => 'images/film3.jpg', 'city' => 'Нижнекамск', 'venue' => 'Парк Нефтехимиков', 'show_time' => now()->addDays(3)->setTime(19, 0), 'venue_capacity' => 120, 'expected_attendees' => 88, 'description' => 'Подростки находят старую киноплёнку и раскрывают загадку, связанную с исчезнувшим режиссёром.'],
            ['title' => 'Маршрут мечты', 'genre' => 'Драма', 'duration' => 118, 'age_rating' => '12', 'poster' => 'images/film4.jpg', 'city' => 'Альметьевск', 'venue' => 'Городской парк', 'show_time' => now()->addDays(4)->setTime(20, 30), 'venue_capacity' => 120, 'expected_attendees' => 102, 'description' => 'Водитель кинофургона помогает жителям разных городов поверить в свои идеи и собрать общий культурный маршрут.'],
            ['title' => 'Каникулы в фургоне', 'genre' => 'Семейный', 'duration' => 91, 'age_rating' => '0', 'poster' => 'images/film5.jpg', 'city' => 'Лениногорск', 'venue' => 'Площадь Ленина', 'show_time' => now()->addDays(5)->setTime(18, 30), 'venue_capacity' => 90, 'expected_attendees' => 67, 'description' => 'Дети случайно становятся волонтёрами передвижного кинотеатра и узнают, как рождается настоящий праздник.'],
            ['title' => 'Ночной сеанс', 'genre' => 'Триллер', 'duration' => 105, 'age_rating' => '16', 'poster' => null, 'city' => 'Бугульма', 'venue' => 'Летняя сцена парка', 'show_time' => now()->addDays(6)->setTime(21, 0), 'venue_capacity' => 90, 'expected_attendees' => 58, 'description' => 'Камерный триллер о последнем сеансе в старом клубе, где каждый зритель знает больше, чем говорит.'],
            ['title' => 'По следам Сабантуя', 'genre' => 'Документальный', 'duration' => 86, 'age_rating' => '6', 'poster' => null, 'city' => 'Азнакаево', 'venue' => 'Центральная площадь', 'show_time' => now()->addDays(7)->setTime(19, 0), 'venue_capacity' => 60, 'expected_attendees' => 49, 'description' => 'Яркое документальное путешествие по традициям Татарстана, музыке, спорту и людям, которые сохраняют праздник живым.'],
            ['title' => 'Крылья над Волгой', 'genre' => 'Мелодрама', 'duration' => 109, 'age_rating' => '12', 'poster' => null, 'city' => 'Заинск', 'venue' => 'Дом культуры Энергетик', 'show_time' => now()->addDays(8)->setTime(19, 30), 'venue_capacity' => 60, 'expected_attendees' => 53, 'description' => 'Тёплая история о встрече двух людей на выездном показе и о том, как маленькое событие меняет большой выбор.'],
            ['title' => 'Последний билет', 'genre' => 'Фантастика', 'duration' => 121, 'age_rating' => '16', 'poster' => null, 'city' => 'Менделеевск', 'venue' => 'Площадь у ДК', 'show_time' => now()->addDays(9)->setTime(20, 0), 'venue_capacity' => 60, 'expected_attendees' => 44, 'description' => 'Фантастический фильм о билете, который позволяет на один вечер попасть в лучший день своего прошлого.'],
            ['title' => 'Добро пожаловать в Мамадыш', 'genre' => 'Комедия', 'duration' => 94, 'age_rating' => '6', 'poster' => null, 'city' => 'Мамадыш', 'venue' => 'Городская набережная', 'show_time' => now()->addDays(10)->setTime(18, 45), 'venue_capacity' => 40, 'expected_attendees' => 35, 'description' => 'Лёгкая комедия о съёмочной группе, которая перепутала адрес и нашла идеальное место для своего фильма.'],
        ];

        $movies = collect($moviesData)->mapWithKeys(function (array $movie) use ($cities) {
            $model = Movie::updateOrCreate(
                ['title' => $movie['title']],
                [
                    'genre' => $movie['genre'],
                    'duration' => $movie['duration'],
                    'age_rating' => $movie['age_rating'],
                    'poster' => $movie['poster'],
                    'description' => $movie['description'],
                    'city_id' => $cities[$movie['city']]->id,
                    'venue' => $movie['venue'],
                    'show_time' => $movie['show_time'],
                    'venue_capacity' => $movie['venue_capacity'],
                    'expected_attendees' => $movie['expected_attendees'],
                ]
            );

            return [$movie['title'] => $model];
        });

        Setting::updateOrCreate(
            ['id' => 1],
            [
                'current_city_id' => $cities['Набережные Челны']->id,
                'ticket_price' => 450,
                'voting_deadline' => now()->addDays(5)->setTime(23, 59),
            ]
        );

        $votes = [
            [$user, 'Маршрут мечты', 'Альметьевск', 3],
            [$user, 'Лето на Каме', 'Елабуга', 2],
            [$user, 'Звёздный автобус', 'Набережные Челны', 4],
            [$admin, 'Звёздный автобус', 'Набережные Челны', 2],
            [$admin, 'Тайна старой мельницы', 'Нижнекамск', 3],
            [$admin, 'Каникулы в фургоне', 'Лениногорск', 2],
            [$guest, 'Лето на Каме', 'Елабуга', 4],
            [$guest, 'Ночной сеанс', 'Бугульма', 2],
            [$guest, 'По следам Сабантуя', 'Азнакаево', 3],
        ];

        foreach ($votes as [$voter, $movieTitle, $cityName, $expectedAttendees]) {
            Vote::updateOrCreate(
                ['user_id' => $voter->id, 'city_id' => $cities[$cityName]->id],
                ['movie_id' => $movies[$movieTitle]->id, 'expected_attendees' => $expectedAttendees]
            );
        }

        $tickets = [
            [$user, 'Маршрут мечты', 'Альметьевск', 2, 'DEMO-ALM-001'],
            [$user, 'Звёздный автобус', 'Набережные Челны', 3, 'DEMO-CHELNY-001'],
            [$guest, 'Лето на Каме', 'Елабуга', 4, 'DEMO-ELABUGA-001'],
            [$guest, 'Тайна старой мельницы', 'Нижнекамск', 2, 'DEMO-NK-001'],
            [$admin, 'Каникулы в фургоне', 'Лениногорск', 1, 'DEMO-LEN-001'],
        ];

        foreach ($tickets as [$buyer, $movieTitle, $cityName, $quantity, $reference]) {
            $movie = $movies[$movieTitle];
            $showDate = $movie->show_time ? $movie->show_time->toDateString() : now()->toDateString();
            $showTime = $movie->show_time ? $movie->show_time->format('H:i') : '19:00';
            $unitPrice = 450;

            Ticket::updateOrCreate(
                ['payment_reference' => $reference],
                [
                    'user_id' => $buyer->id,
                    'city_id' => $cities[$cityName]->id,
                    'movie_id' => $movie->id,
                    'show_date' => $showDate,
                    'show_time' => $showTime,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                    'status' => 'purchased',
                    'payment_method' => 'demo-card',
                    'qr_token' => (string) Str::uuid(),
                ]
            );
        }
    }
}
