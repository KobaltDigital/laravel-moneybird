<?php

namespace Kobalt\LaravelMoneybird\OAuth2;

use GuzzleHttp\Client;
use Kobalt\LaravelMoneybird\Exceptions\AuthenticationException;

class MoneybirdOAuth
{
    private const AUTHORIZE_URL = 'https://moneybird.com/oauth/authorize';
    private const TOKEN_URL = 'https://moneybird.com/oauth/token';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private array $scopes;
    private Client $httpClient;

    public function __construct(string $clientId, string $clientSecret, string $redirectUri, array $scopes = ['sales_invoices', 'documents', 'estimates'])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->scopes = $scopes;

        $this->httpClient = new Client();
    }

    /**
     * Get the authorization URL for the OAuth2 flow
     */
    public function getAuthorizationUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes)
        ];

        return self::AUTHORIZE_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange the authorization code for an access token
     *
     * @throws AuthenticationException
     */
    public function getAccessToken(string $code): array
    {
        try {
            $response = $this->httpClient->post(self::TOKEN_URL, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                    'grant_type' => 'authorization_code'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            throw new AuthenticationException('Failed to get access token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh an access token using a refresh token
     *
     * @throws AuthenticationException
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->post(self::TOKEN_URL, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            throw new AuthenticationException('Failed to refresh access token: ' . $e->getMessage());
        }
    }
}