<?php

namespace Pektiyaz\LaravelEventEngine\CanValues;

class EventCanValue
{
    public function __construct(
        public bool $can,
        public ?string $reason = null,
        public mixed $listener = null
    ) {}

    public function toArray(): array
    {
        return ["can" => $this->can, "reason" => $this->reason, 'listener' => $this->listener];
    }
}