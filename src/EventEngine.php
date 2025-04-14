<?php
namespace Pektiyaz\LaravelEventEngine;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Pektiyaz\LaravelEventEngine\AskValues\EventAskResult;
use Pektiyaz\LaravelEventEngine\AskValues\EventAskValue;
use Pektiyaz\LaravelEventEngine\CanValues\EventCanResult;
use Pektiyaz\LaravelEventEngine\CanValues\EventCanValue;
use Pektiyaz\LaravelEventEngine\DoValues\EventDoResult;
use Pektiyaz\LaravelEventEngine\DoValues\EventDoValue;

class EventEngine
{
    public static function can(object $event): EventCanResult
    {
        $reason = null;
        $can = false;
        $answers = [];


        $results = Event::dispatch($event);

        foreach ($results as $listenerResult) {
            if (!$listenerResult instanceof EventCanValue) {
                throw new \RuntimeException('Invalid listener response. Listeners for "can" should return EventCanValue!');
            }
            if(!$listenerResult->can && !$can){
                $can = $listenerResult->can;
                $reason = $listenerResult->data;
            }
            $answers[] = $listenerResult;
        }

        if(!$reason){
            foreach($answers as $answer){
                if($answer->can && $answer->data){
                    $can = true;
                    $reason = $answer->data;
                }
            }
        }

        return new EventCanResult($can, $reason, $answers);
    }

    public static function do(object $event): EventDoResult
    {
        $results = Event::dispatch($event);
        $answers = [];
        $success = false;
        $data = null;

        foreach ($results as $listenerResult) {
            if (!$listenerResult instanceof EventDoValue) {
                throw new \RuntimeException('Invalid listener response. Listeners for "do" should return EventDoValue!');
            }
            $answers[] = $listenerResult;

            if(!$data && $listenerResult->success){
                $success = true;
                $data = $listenerResult->data;
            }
        }

        if(!$data){
            foreach($answers as $answer){
                if(!$answer->success && $answer->data){
                    $success = false;
                    $data = $answer->data;
                }
            }
        }
        return new EventDoResult($success,$data, $answers);
    }

    public static function doAtomic(object $event): EventDoResult
    {
        DB::beginTransaction();
        try{
            $result = EventEngine::do($event);

            if($result->success){
                DB::commit();
                return $result;
            }else{
                DB::rollBack();
                return $result;
            }
        }catch (\Throwable $throwable){
            DB::rollBack();
            throw $throwable;
        }
    }

    public static function after(object $event): void
    {
        Event::dispatch($event);
    }

    public static function ask(object $event): EventAskResult
    {
        $results = Event::dispatch($event);
        $data = [];
        $answers = [];
        $success = false;

        foreach ($results as $listenerResult) {
            if (!$listenerResult instanceof EventAskValue) {
                throw new \RuntimeException('Invalid listener response. Listeners for "ask" should return EventAskValue!');
            }

            if($listenerResult->success && !$success){
                $success = true;
            }
            $answers[] = $listenerResult;
            $data = array_merge($data, $listenerResult->data);
        }

        return new EventAskResult($success, $data, $answers);
    }

}
