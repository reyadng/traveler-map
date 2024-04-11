<?php

namespace App\Processors;

use App\Models\Traveler;
use App\Models\TravelerLocation;
use Stidges\CountryFlags\CountryFlag;

class StatisticsProcessor
{
    public function __construct(
        private readonly CountryFlag $countryFlag
    ) {
    }

    public function buildMessage(): ?string
    {
        $travelers = Traveler::all();
        $travelerLocations = TravelerLocation::all();
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
        $message = implode("\n\n", $lines);

        return $message ?? null;
    }
}
