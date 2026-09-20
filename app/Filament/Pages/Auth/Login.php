<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends BaseLogin
{
    protected const MAX_LOGIN_ATTEMPTS = 5;

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimitLoginAccount();
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $response = parent::authenticate();

        if ($response !== null) {
            RateLimiter::clear($this->loginRateLimitKey());
        }

        return $response;
    }

    protected function rateLimitLoginAccount(): void
    {
        if (RateLimiter::tooManyAttempts($this->loginRateLimitKey(), self::MAX_LOGIN_ATTEMPTS)) {
            throw new TooManyRequestsException(
                static::class,
                'authenticate',
                (string) request()->ip(),
                RateLimiter::availableIn($this->loginRateLimitKey()),
            );
        }

        RateLimiter::hit($this->loginRateLimitKey(), 60);
    }

    protected function loginRateLimitKey(): string
    {
        $email = Str::lower(Str::transliterate((string) ($this->form->getState()['email'] ?? '')));

        return 'login:account:'.hash('sha256', $email);
    }
}
