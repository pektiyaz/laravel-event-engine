<?php

namespace Pektiyaz\LaravelEventEngine\AskValues;

class EventAskResult
{
    public function __construct(
        public bool  $success,
        public mixed $data = null,
        public array $answers = []) {}


    public function toArray(): array
    {
        $answers = [];
        foreach ($this->answers as $answer) {
            $answers[] = $answer->toArray();
        }
        return [
            "success"     => $this->success,
            "data"    => $this->data,
            "answers" => $answers
        ];
    }
}