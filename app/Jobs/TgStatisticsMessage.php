<?php

namespace App\Jobs;

use App\Constants\CmdConstants;
use App\Processors\StatisticsProcessor;
use App\Processors\TgCallback;
use BotMan\BotMan\BotMan;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TgStatisticsMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const int MAX_ROW_SIZE = 8;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly BotMan $botMan,
        private readonly int $chatId,
        private readonly int|null $messageId = null,
        private readonly array|null $callbackData = null,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(StatisticsProcessor $statisticsProcessor, TgCallback $tgCallback): void
    {
        try {
            $pageNumber = $this->callbackData['p'] ?? 1;
            $message = $statisticsProcessor->buildMessage($pageNumber);

            $paginationButtons = [];
            for ($i = 0; $i < $statisticsProcessor->pageAmount(); $i++) {
                $pageNumberView = $i + 1;
                if ($pageNumberView === $pageNumber) {
                    $pageNumberView = sprintf('*%s*', $pageNumberView);
                }
                $paginationButtons[] = KeyboardButton::create($pageNumberView)
                    ->callbackData($tgCallback->generatePayload(CmdConstants::CMD_STATS, ['p' => $i + 1]));
            }

            $refreshButton = KeyboardButton::create('⟳')
                ->callbackData($tgCallback->generatePayload(CmdConstants::CMD_STATS, ['p' => 1]));
            $keyboard = Keyboard::create()
                ->type(Keyboard::TYPE_INLINE);

            foreach (array_chunk($paginationButtons, self::MAX_ROW_SIZE) as $paginationButtonsRow) {
                $keyboard->addRow(...$paginationButtonsRow);
            }
            $keyboard = $keyboard
                ->addRow($refreshButton)
                ->toArray();

            if (!$message) {
                $this->botMan->sendRequest('editMessageText', [
                    'chat_id' => $this->chatId,
                    'message_id' => $this->messageId,
                    'text' => 'Пустота',
                    'reply_markup' => $keyboard['reply_markup'],
                ]);
            }
            if ($this->messageId) {
                $this->botMan->sendRequest('editMessageText', [
                    'chat_id' => $this->chatId,
                    'message_id' => $this->messageId,
                    'text' => $message,
                    'reply_markup' => $keyboard['reply_markup'],
                ]);
                return;
            }
            $this->botMan->sendRequest('sendMessage', [
                'chat_id' => $this->chatId,
                'text' => $message,
                'reply_markup' => $keyboard['reply_markup'],
            ]);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
