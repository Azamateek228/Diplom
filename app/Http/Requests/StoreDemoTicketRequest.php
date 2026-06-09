<?php

namespace App\Http\Requests;

use App\Models\City;
use App\Models\Movie;
use App\Models\Ticket;
use App\Services\DemoCardValidator;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDemoTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'city_id' => ['required', 'exists:cities,id'],
            'movie_id' => ['required', 'exists:movies,id'],
            'show_date' => ['required', 'date'],
            'show_time' => ['required', 'date_format:H:i'],
            'quantity' => ['required', 'integer', 'min:1'],
            'card_number' => ['required', 'string', 'max:32'],
            'card_holder' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s-]+$/u'],
            'card_expiry' => ['required', 'string', 'max:7'],
            'card_cvv' => ['required', 'regex:/^\d{3,4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'Выберите город показа.',
            'city_id.exists' => 'Выбранный город не найден.',
            'movie_id.required' => 'Выберите фильм.',
            'movie_id.exists' => 'Выбранный фильм не найден.',
            'show_date.required' => 'Укажите дату показа.',
            'show_time.date_format' => 'Время показа должно быть в формате ЧЧ:ММ.',
            'quantity.required' => 'Укажите количество билетов.',
            'quantity.integer' => 'Количество билетов должно быть целым числом.',
            'quantity.min' => 'Нужно выбрать хотя бы один билет.',
            'card_number.required' => 'Введите номер карты.',
            'card_number.max' => 'Номер карты слишком длинный.',
            'card_holder.required' => 'Введите имя держателя карты.',
            'card_holder.min' => 'Имя держателя карты слишком короткое.',
            'card_holder.max' => 'Имя держателя карты слишком длинное.',
            'card_holder.regex' => 'В имени держателя допустимы только буквы, пробелы и дефисы.',
            'card_expiry.required' => 'Введите срок действия карты.',
            'card_expiry.max' => 'Срок действия карты должен быть в формате MM/YY или MM/YYYY.',
            'card_cvv.required' => 'Введите CVV.',
            'card_cvv.regex' => 'CVV должен состоять из 3 или 4 цифр.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->count() > 0) {
                return;
            }

            if (! DemoCardValidator::hasValidNumber((string) $this->input('card_number'))) {
                $validator->errors()->add('card_number', 'Введите корректный демо-номер карты: 16–19 цифр и верная контрольная сумма Луна.');
            }

            if (! DemoCardValidator::hasValidExpiry((string) $this->input('card_expiry'))) {
                $validator->errors()->add('card_expiry', 'Срок действия карты указан неверно или уже истёк.');
            }

            $availableTickets = $this->availableTickets();
            $quantity = (int) $this->input('quantity');

            if ($availableTickets <= 0) {
                $validator->errors()->add('quantity', 'На этот сеанс билеты распроданы.');
            } elseif ($quantity > $availableTickets) {
                $validator->errors()->add('quantity', 'Недостаточно билетов. Доступно: ' . $availableTickets . '.');
            }
        });
    }

    public function validatedForTicket(): array
    {
        $validated = $this->validated();
        $validated['show_date'] = Carbon::parse($validated['show_date'])->toDateString();
        $validated['card_number'] = DemoCardValidator::normalizeNumber($validated['card_number']);
        $validated['card_holder'] = preg_replace('/\s+/', ' ', trim($validated['card_holder']));

        return $validated;
    }

    public function availableTickets(): int
    {
        $city = City::find($this->integer('city_id'));
        $movie = Movie::find($this->integer('movie_id'));

        if (! $city || ! $movie || ! $this->input('show_date')) {
            return 0;
        }

        $showDate = Carbon::parse($this->input('show_date'))->toDateString();
        $capacity = self::capacityByCity($city);
        $sold = (int) Ticket::where('city_id', $city->id)
            ->where('movie_id', $movie->id)
            ->whereDate('show_date', $showDate)
            ->where('status', 'purchased')
            ->sum('quantity');

        return max(0, $capacity - $sold);
    }

    public static function capacityByCity(City $city): int
    {
        $population = (int) ($city->population ?? 40000);

        if ($population <= 20000) {
            return 40;
        }

        if ($population <= 50000) {
            return 60;
        }

        if ($population <= 100000) {
            return 90;
        }

        return 120;
    }
}
