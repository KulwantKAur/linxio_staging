<?php

namespace App\Service\Chat;

use App\Entity\User;

class ChatResponse
{
    private string $event;
    private ?array $data = [];
    private ?User $user = null;

    public function __construct(array $fields = [])
    {
        $this->event = $fields['event'] ?? null;
        $this->data = $fields['data'] ?? null;
        $this->user = $fields['user'] ?? null;
    }

    /**
     * @return mixed|string|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed|string|null $event
     */
    public function setEvent($event): void
    {
        $this->event = $event;
    }

    /**
     * @return array|mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|mixed|null $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'event' => $this->getEvent(),
            'user' => $this->getUser() ? $this->getUser()->toArray(User::SIMPLE_VALUES_CHAT) : null,
            'data' => $this->getData(),
        ];
    }

    /**
     * @param array $fields
     * @return array
     * @throws \Exception
     */
    public static function createFromRaw(array $fields = []): array
    {
        return (new self($fields))->toArray();
    }
}
