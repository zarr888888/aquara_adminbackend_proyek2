<?php

namespace Tests\Support;

use App\Support\OtpService;

/**
 * Capture OTP delivery in tests without contacting email or WhatsApp providers.
 */
class CapturingOtpService extends OtpService
{
    public string $code;

    protected function deliver(string $channel, string $target, string $code, string $purpose): void
    {
        $this->code = $code;
    }
}