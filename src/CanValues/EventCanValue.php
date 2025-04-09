<?php

namespace Pektiyaz\LaravelEventEngine\CanValues;

class EventCanValue
{
    public function __construct(
        public bool  $can,
        public mixed $data = null,
        public mixed $listener = null
    ) {}

    public function toArray(): array
    {
        return ["can" => $this->can, "data" => $this->data, 'listener' => $this->listener];
    }
}