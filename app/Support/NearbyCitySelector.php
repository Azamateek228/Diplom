<?php

namespace App\Support;

use App\Models\City;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NearbyCitySelector
{
    public static function mapCities(int $limit = 10, ?int $ensureCityId = null): Collection
    {
        return self::appendEnsuredCity(self::fullRoute(), $ensureCityId);
    }

    public static function fullRoute(): Collection
    {
        return self::applyRouteOrdering(
            City::query()
                ->withCount('votes')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
        )->get()->values();
    }

    public static function votedRoute(): Collection
    {
        return self::applyRouteOrdering(
            City::query()
                ->withCount('votes')
                ->whereHas('votes')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
        )->get()->values();
    }

    public static function actualRoute(): Collection
    {
        return Vote::count() > 0
            ? self::votedRoute()
            : self::fullRoute();
    }

    public static function actualRouteType(): string
    {
        return Vote::count() > 0 ? 'short' : 'long';
    }

    public static function actualRouteLabel(): string
    {
        return self::actualRouteType() === 'short'
            ? 'Тип маршрута: короткий, построен по городам с голосами зрителей'
            : 'Тип маршрута: длинный, так как голосов пока нет';
    }

    public static function naberezhnyeChelnyWithNearest(int $nearest = 10, ?int $ensureCityId = null): Collection
    {
        return self::appendEnsuredCity(self::fullRoute()->take($nearest + 1)->values(), $ensureCityId);
    }

    public static function orderedRoute(Collection $cities, ?int $currentCityId = null): Collection
    {
        if ($cities->isEmpty()) {
            return collect();
        }

        return $cities
            ->sortBy([
                fn ($a, $b) => ($a->route_order ?? PHP_INT_MAX) <=> ($b->route_order ?? PHP_INT_MAX),
                fn ($a, $b) => $a->id <=> $b->id,
                fn ($a, $b) => strnatcasecmp((string) $a->name, (string) $b->name),
            ])
            ->values();
    }

    private static function applyRouteOrdering(Builder $query): Builder
    {
        if (self::hasRouteOrder()) {
            $query
                ->orderByRaw('CASE WHEN route_order IS NULL THEN 1 ELSE 0 END')
                ->orderBy('route_order');
        }

        return $query
            ->orderBy('id')
            ->orderBy('name');
    }

    private static function appendEnsuredCity(Collection $cities, ?int $ensureCityId): Collection
    {
        if (!$ensureCityId || $cities->contains(fn ($city) => (int) $city->id === (int) $ensureCityId)) {
            return self::orderedRoute($cities);
        }

        $ensuredCity = City::withCount('votes')->find($ensureCityId);
        if (!$ensuredCity) {
            return self::orderedRoute($cities);
        }

        return self::orderedRoute($cities->push($ensuredCity));
    }

    private static function hasRouteOrder(): bool
    {
        static $hasRouteOrder = null;

        if ($hasRouteOrder === null) {
            $hasRouteOrder = Schema::hasColumn('cities', 'route_order');
        }

        return $hasRouteOrder;
    }
}
