<?php

namespace Kobalt\LaravelMoneybird\Resources;

use Illuminate\Support\Collection;
use GuzzleHttp\Exception\GuzzleException;
use Kobalt\LaravelMoneybird\MoneybirdClient;

class MoneybirdContact extends MoneybirdClient
{
    public function __construct($user = null, ?string $administrationId = null)
    {
        parent::__construct($user, $administrationId);
    }

    /**
     * Get all contacts
     *
     * @throws GuzzleException
     */
    public function all(array $query = []): Collection
    {
        return $this->get('contacts', $query);
    }

    /**
     * Get a specific contact
     *
     * @throws GuzzleException
     */
    public function find(string $id): Collection
    {
        return $this->get("contacts/{$id}");
    }

    /**
     * Create a new contact
     *
     * @throws GuzzleException
     */
    public function create(array $data): Collection
    {
        return $this->post('contacts', ['contact' => $data]);
    }

    /**
     * Update a contact
     *
     * @throws GuzzleException
     */
    public function update(string $id, array $data): Collection
    {
        return $this->put("contacts/{$id}", ['contact' => $data]);
    }

    /**
     * Delete a contact
     *
     * @throws GuzzleException
     */
    public function delete(string $id): bool
    {
        return $this->delete("contacts/{$id}");
    }

    /**
     * Get all contact people for a contact
     *
     * @throws GuzzleException
     */
    public function contactPeople(string $contactId): Collection
    {
        return $this->get("contacts/{$contactId}/contact_people");
    }

    /**
     * Create a new contact person
     *
     * @throws GuzzleException
     */
    public function createContactPerson(string $contactId, array $data): Collection
    {
        return $this->post("contacts/{$contactId}/contact_people", ['contact_person' => $data]);
    }

    /**
     * Update a contact person
     *
     * @throws GuzzleException
     */
    public function updateContactPerson(string $contactId, string $contactPersonId, array $data): Collection
    {
        return $this->put(
            "contacts/{$contactId}/contact_people/{$contactPersonId}",
            ['contact_person' => $data]
        );
    }

    /**
     * Delete a contact person
     *
     * @throws GuzzleException
     */
    public function deleteContactPerson(string $contactId, string $contactPersonId): bool
    {
        return $this->delete("contacts/{$contactId}/contact_people/{$contactPersonId}");
    }
}