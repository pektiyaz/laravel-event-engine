<?php

namespace Pektiyaz\LaravelEventEngine\CanValues;

class EventCanResult
{
    public function __construct(
        public bool  $can,
        public mixed $data = null,
        public array $answers = []) {}


    public function toArray(): array
    {
        $answers = [];
        foreach ($this->answers as $answer) {
            $answers[] = $answer->toArray();
        }
        return [
            "can"     => $this->can,
            "data"    => $this->data,
            "answers" => $answers
        ];
    }
}