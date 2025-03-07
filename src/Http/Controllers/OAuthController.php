<?php

namespace Kobalt\LaravelMoneybird\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Kobalt\LaravelMoneybird\Exceptions\AuthenticationException;
use Kobalt\LaravelMoneybird\Facades\Moneybird;
use Carbon\Carbon;

class OAuthController extends Controller
{
    /**
     * Redirect the user to the Moneybird authorization page
     */
    public function redirect()
    {
        return redirect()->away(Moneybird::getAuthorizationUrl());
    }

    /**
     * Handle the callback from Moneybird
     */
    public function callback(Request $request)
    {
        try {
            if ($request->has('error')) {
                throw new AuthenticationException(
                    "OAuth error: {$request->get('error_description', $request->get('error'))}"
                );
            }

            $code = $request->get('code');
            if (empty($code)) {
                throw new AuthenticationException('No authorization code provided');
            }

            $tokens = Moneybird::getAccessToken($code);

            // Calculate expires_at from created_at
            $expiresAt = Carbon::createFromTimestamp($tokens['created_at'])
                ->addHours(24); // Moneybird tokens expire after 24 hours

            $user = Auth::user();
            $user->update([
                'moneybird_access_token' => $tokens['access_token'],
                'moneybird_refresh_token' => $tokens['refresh_token'],
                'moneybird_token_expires_at' => $expiresAt
            ]);

            ray('update');

            return redirect()
                ->intended(config('moneybird.redirect_after_connect', '/'))
                ->with('success', 'Successfully connected to Moneybird!');

        } catch (AuthenticationException $e) {
            return redirect()
                ->route('home')
                ->with('error', 'Failed to connect to Moneybird: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect the user from Moneybird
     */
    public function disconnect(Request $request)
    {
        Auth::user()->disconnectMoneybird();

        return redirect()
            ->back()
            ->with('success', 'Successfully disconnected from Moneybird');
    }
}
