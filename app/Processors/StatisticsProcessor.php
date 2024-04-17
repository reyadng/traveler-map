<?php

namespace App\Processors;

use App\Models\Traveler;
use App\Models\TravelerLocation;
use Stidges\CountryFlags\CountryFlag;

class StatisticsProcessor
{
    public const int PAGE_SIZE = 10;

    public function __construct(
        private readonly CountryFlag $countryFlag
    ) {
    }

    public function count(): int
    {
        return Traveler::count();
    }

    public function pageAmount(): int
    {
        return ceil($this->count() / self::PAGE_SIZE);
    }

    public function buildMessage(int $page): ?string
    {
        $travelerLocations = TravelerLocation::all();
        $messages = [];
        Traveler::orderBy('updated_at', 'desc')
            ->chunk(self::PAGE_SIZE, function ($travelers) use ($travelerLocations, &$messages) {
                $lines = [];
                foreach ($travelers as $traveler) {
                    $lastLocation = $travelerLocations
                        ->where('id', $traveler->traveler_location_id)
                        ->first();
                    $line = '';
                    $line .= implode(' ', [$traveler->first_name, $traveler->last_name]);
                    if ($traveler->username) {
                        $line .= " (@{$traveler->username})";
                    }
                    $line .= "\n";
                    if ($lastLocation->country_code) {
                        $line .= $this->countryFlag->get($lastLocation->country_code);
                        $line .= ' ';
                        $line .= "{$lastLocation->country}";
                        if ($lastLocation->locality) {
                            $line .= ", {$lastLocation->locality}";
                        }
                    } else {
                        $line .= '🙈 на нейтральных территориях';
                    }
                    $lines[] = $line;
                }
                $messages[] = implode("\n\n", $lines);
            });

        return $messages[$page - 1] ?? null;
    }
}
