<?php

namespace Kobalt\LaravelMoneybird\Traits;

use Carbon\Carbon;
use Kobalt\LaravelMoneybird\Facades\Moneybird;

trait HasMoneybirdConnection
{
    protected static function bootHasMoneybirdConnection()
    {
        static::retrieved(function ($model) {
            $model->mergeFillable([
                'moneybird_access_token',
                'moneybird_refresh_token',
                'moneybird_token_expires_at'
            ]);
        });
    }

    public function needsTokenRefresh(): bool
    {
        if (!$this->moneybird_token_expires_at) {
            return true;
        }

        return Carbon::parse($this->moneybird_token_expires_at)
            ->subMinutes(5)
            ->isPast();
    }

    public function getMoneybirdToken(): string
    {
        if ($this->needsTokenRefresh() && $this->moneybird_refresh_token) {
            $this->refreshMoneybirdToken();
        }

        return $this->moneybird_access_token;
    }

    public function refreshMoneybirdToken(): void
    {
        $tokens = Moneybird::refreshAccessToken($this->moneybird_refresh_token);

        // Calculate expires_at from created_at
        $expiresAt = Carbon::createFromTimestamp($tokens['created_at'])
        // ->addHours(24); // Moneybird tokens expire after 24 hours
            ->addSeconds(24); // Moneybird tokens expire after 24 hours

        $this->update([
            'moneybird_access_token' => $tokens['access_token'],
            'moneybird_refresh_token' => $tokens['refresh_token'],
            'moneybird_token_expires_at' => $expiresAt
        ]);
    }

    public function disconnectMoneybird(): void
    {
        $this->update([
            'moneybird_access_token' => null,
            'moneybird_refresh_token' => null,
            'moneybird_token_expires_at' => null
        ]);
    }

    public function isConnectedtoMoneybird(): bool
    {
        return !empty($this->moneybird_access_token) && !empty($this->moneybird_refresh_token);
    }
}