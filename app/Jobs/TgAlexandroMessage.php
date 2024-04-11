<?php

namespace App\Jobs;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class TgAlexandroMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const STICKERS = [
        'CAACAgIAAxkBAAPFZhZu7gubiVY2NLzzzeGd6Z8XhZYAAnwWAAIQcclJdGRx1wJcENg0BA',
        'CAACAgIAAxkBAAPEZhZu7JRPYLQduUCFEXRkfHiOXUsAAl0dAAO8yEmgTk7EA9g4vTQE',
        'CAACAgIAAxkBAAPDZhZu6wJYZA4CZA82rFcQ-dWVs7UAAtAZAAKcOLBJ8yH_317ECNg0BA',
        'CAACAgIAAxkBAAPCZhZu6Bg0VcyNY7CpFaS06jrZdsUAAtoYAAL6gblJePhMZTxvYH00BA',
        'CAACAgIAAxkBAAPBZhZu57OdEGX-Grq9E95nKWEyzYAAAjEgAAJ885hJBLvc4hfEXSo0BA',
        'CAACAgIAAxkBAAPAZhZu5DArFp6nfkb5iztkuhh-HwUAAk8cAALl6ZFJzw91KE2Lg3Q0BA',
        'CAACAgIAAxkBAAO_ZhZu4sVdzNHURUm86QswrUB-PnsAAmYhAAJCQJFJnAdBKRudXOo0BA',
        'CAACAgIAAxkBAAO-ZhZu4PjIIi5fUn22RRLaYw1CZoAAAjgeAAIqapBJ6PnR9HdzcaU0BA',
        'CAACAgIAAxkBAAO9ZhZu3bo8K9BxUOs0PgtUTfDYd_0AAlwXAAKNKZBJY9w0kLLNGY80BA',
        'CAACAgIAAxkBAAO8ZhZu2mgQYJCkdxHRfzyctw5CB2IAAoseAAIZypFJ5Wz_UnRfOtk0BA',
        'CAACAgIAAxkBAAPGZhZvtdzLRx0zWDUrBLpqoBP4wEgAAkAfAAKLOJFJIlcmuYRPW4s0BA',
    ];


    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly BotMan $botMan,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $chatId = $this->botMan->getMessage()->getPayload()['chat']['id'];
            if ($chatId) {
                $this->botMan->sendRequest('sendSticker', [
                    'sticker' => self::STICKERS[array_rand(self::STICKERS)],
                    'chat_id' => $chatId,
                ]);
            }
        } catch (\Exception$e) {
            Log::error($e);
        }
    }
}
