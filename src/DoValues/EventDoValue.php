<?php

namespace Pektiyaz\LaravelEventEngine\DoValues;

class EventDoValue
{
    public function __construct(
        public bool  $success,
        public mixed $data = null,
        public mixed $listener = null
    ) {}


    public function toArray(): array
    {
        return ["success" => $this->success, "data" => $this->data, 'listener' => $this->listener];
    }
}