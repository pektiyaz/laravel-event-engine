<?php

namespace Pektiyaz\LaravelEventEngine\AskValues;

class EventAskValue
{
    public function __construct(
        public bool  $success,
        public array $data,
        public mixed $listener = null
    ) {}

    public function toArray(): array
    {
        return ["can" => $this->success, "data" => $this->data, 'listener' => $this->listener];
    }
}