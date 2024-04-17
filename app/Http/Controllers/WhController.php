<?php

namespace App\Http\Controllers;

use App\Jobs\TgAlexandroMessage;
use App\Jobs\TgMessage;
use App\Jobs\TgStatisticsMessage;
use App\Models\Traveler;
use App\Models\TravelerLocation;
use App\Processors\StatisticsProcessor;
use App\Processors\TgCallback;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\UserInterface;
use BotMan\BotMan\Messages\Attachments\Location;
use Geocoder\Geocoder;
use Geocoder\Query\ReverseQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhController extends Controller
{
    public function index(Request $request, BotMan $botman, Geocoder $geocoder, StatisticsProcessor $statisticsProcessor, TgCallback $tgCallback)
    {
        try {
            $this->handleMessage($request, $botman, $statisticsProcessor, $geocoder, $tgCallback);
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }

    private function saveLocation(Traveler $traveler, Location $location, Geocoder $geocoder): void
    {
        $reverseQuery = ReverseQuery::fromCoordinates($location->getLatitude(), $location->getLongitude());
        $geocoderResult = $geocoder
            ->reverseQuery($reverseQuery)
            ->first();

        $travelerLocation = TravelerLocation::create([
            'id' => Str::uuid()->toString(),
            'tg_id' => $traveler->tg_id,
            'latitude' => $location->getLatitude(),
            'longitude' => $location->getLongitude(),
            'country' => $geocoderResult?->getCountry()?->getName(),
            'locality' => $geocoderResult?->getLocality(),
            'country_code' => $geocoderResult?->getCountry()?->getCode()
        ]);
        $traveler->traveler_location_id = $travelerLocation->id;
        $traveler->save();
    }

    private function getOrCreateTraveler(UserInterface $user): Traveler
    {
        return Traveler::where('tg_id', (string)$user->getId())->first()
            ?? Traveler::create([
                'tg_id' => (string)$user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);
    }

    private function isCommand(BotMan $bot, string $command): bool
    {
        $payload = $bot->getMessage()?->getPayload();
        $entityType = $payload['entities'][0]['type'] ?? null;
        $text = $payload['text'] ?? '';

        return $entityType === 'bot_command' && strstr($text, $command);
    }

    /**
     * @param BotMan $botman
     * @param StatisticsProcessor $statisticsProcessor
     * @param Geocoder $geocoder
     * @return void
     */
    public function handleMessage(Request $request, BotMan $botman, StatisticsProcessor $statisticsProcessor, Geocoder $geocoder, TgCallback $tgCallback): void
    {
        $data = $request->all();
        $callbackDataPayload = $data['callback_query']['data'] ?? null;
        if ($callbackDataPayload) {
            $callbackData = $tgCallback->generateCallbackDataFromPayload($callbackDataPayload);
        }
        $callbackCmd = $callbackData['cmd'] ?? null;

        if ($callbackCmd === 'stats') {
            TgStatisticsMessage::dispatch(
                $botman,
                $data['callback_query']['message']['chat']['id'],
                $data['callback_query']['message']['message_id'],
                $callbackData['payload']
            );
            return;
        }
        $botman->hears('/start', function ($bot) {
            $chatId = $bot->getMessage()->getPayload()['chat']['id'];
            if ($chatId < 0) {
                return;
            }
            $message = 'Открой телеграм на телефоне, нажми на скрепочку и скинь мне свою локацию';
            TgMessage::dispatch($bot, $message);
        });
        $botman->hears('', function (BotMan $bot) use ($data) {
            if ($this->isCommand($bot, '/whereiswho')) {
                TgStatisticsMessage::dispatch($bot, $data['message']['chat']['id']);
            } elseif ($this->isCommand($bot, '/alexandro')) {
                TgAlexandroMessage::dispatch($bot);
            }

        });
        $botman->receivesLocation(function (BotMan $bot, Location $location) use ($geocoder) {
            $chatId = $bot->getMessage()->getPayload()['chat']['id'];
            if ($chatId < 0) {
                return;
            }
            $traveler = $this->getOrCreateTraveler($bot->getUser());
            $this->saveLocation($traveler, $location, $geocoder);
            TgMessage::dispatch($bot, 'Сохранил');
        });
        $botman->listen();
    }
}
