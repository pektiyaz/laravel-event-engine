<?php

namespace Pektiyaz\LaravelEventEngine\CanValues;

class EventCanResult
{
    public function __construct(
        public bool    $can,
        public ?string $reason = null,
        public array   $answers = []) {}


    public function toArray(): array
    {
        $answers = [];
        foreach ($this->answers as $answer) {
            $answers[] = $answer->toArray();
        }
        return [
            "can"     => $this->can,
            "reason"  => $this->reason,
            "answers" => $answers
        ];
    }
}