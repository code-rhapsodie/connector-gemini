<?php

declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini\ActionHandler;

use RuntimeException;

trait ResponseFormatter
{
    /**
     * @param array $data
     *
     * @return non-empty-array<string>
     */
    public function format(array $data): array
    {
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new RuntimeException('Unable to create response from response data.');
        }

        return [$data['candidates'][0]['content']['parts'][0]['text']];
    }
}
