<?php

namespace Kobalt\LaravelMoneybird;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Kobalt\LaravelMoneybird\Exceptions\AuthenticationException;
use Kobalt\LaravelMoneybird\Exceptions\MoneybirdException;
use Kobalt\LaravelMoneybird\Exceptions\NotFoundException;
use Kobalt\LaravelMoneybird\Exceptions\ValidationException;

class MoneybirdClient
{
    private const API_URL = 'https://moneybird.com/api/v2';

    private Client $httpClient;
    private string $administrationId;
    private $user;

    public function __construct($user = null, ?string $administrationId = null)
    {
        $this->administrationId = $administrationId ?? config('moneybird.administration_id');

        if (empty($this->administrationId)) {
            throw new MoneybirdException('No administration ID provided and none set in config');
        }

        $this->user = $user;
        $this->initializeHttpClient();
    }

    private function initializeHttpClient(): void
    {
        $user = $this->user ?? Auth::user();
        $accessToken = $user->getMoneybirdToken();

        $this->httpClient = new Client([
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function setUser($user): self
    {
        $this->user = $user;
        $this->initializeHttpClient();
        return $this;
    }

    /**
     * @throws MoneybirdException|GuzzleException
     */
    protected function get(string $endpoint, array $query = []): Collection
    {
        try {
            $response = $this->httpClient->get($this->buildUrl($endpoint), [
                'query' => $query,
            ]);

            return collect(json_decode($response->getBody()->getContents(), true));
        } catch (ClientException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * @throws MoneybirdException|GuzzleException
     */
    protected function post(string $endpoint, array $data = []): Collection
    {
        try {
            $response = $this->httpClient->post($this->buildUrl($endpoint), [
                'json' => $data,
            ]);

            return collect(json_decode($response->getBody()->getContents(), true));
        } catch (ClientException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * @throws MoneybirdException|GuzzleException
     */
    protected function put(string $endpoint, array $data = []): Collection
    {
        try {
            $response = $this->httpClient->put($this->buildUrl($endpoint), [
                'json' => $data,
            ]);

            return collect(json_decode($response->getBody()->getContents(), true));
        } catch (ClientException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * @throws MoneybirdException|GuzzleException
     */
    protected function delete(string $endpoint): bool
    {
        try {
            $response = $this->httpClient->delete($this->buildUrl($endpoint));

            return $response->getStatusCode() === 204;
        } catch (ClientException $e) {
            $this->handleRequestException($e);
        }
    }

    private function buildUrl(string $endpoint): string
    {
        return self::API_URL . "/{$this->administrationId}/{$endpoint}.json";
    }

    /**
     * @throws MoneybirdException
     * @throws ValidationException
     * @throws NotFoundException
     * @throws AuthenticationException
     */
    private function handleRequestException(ClientException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        $message = $body['error'] ?? $e->getMessage();
        $errors = $body['errors'] ?? [];

        switch ($statusCode) {
            case 401:
                throw (new AuthenticationException('Invalid access token'))
                    ->setErrors($errors);
            case 404:
                throw (new NotFoundException('Resource not found'))
                    ->setErrors($errors);
            case 422:
                throw (new ValidationException('Validation failed'))
                    ->setErrors($errors);
            default:
                throw (new MoneybirdException($message))
                    ->setErrors($errors);
        }
    }
}