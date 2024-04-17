<?php

namespace App\Processors;

use App\Constants\CmdConstants;

class TgCallback
{
    /**
     * @param string $data 'cmd' key is required
     * @return array|null
     * @see CmdConstants
     */
    public function generateCallbackDataFromPayload(string $data): ?array
    {
        return json_decode($data, true);
    }

    /**
     * @param string $cmd
     * @param array $payload
     * @return string
     * @throws \JsonException
     * @see CmdConstants
     */
    public function generatePayload(string $cmd, array $payload): string
    {
        return json_encode([
            'cmd' => $cmd,
            'payload' => $payload,
        ], JSON_THROW_ON_ERROR);
    }


}
