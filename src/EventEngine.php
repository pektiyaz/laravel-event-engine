<?php
namespace Pektiyaz\LaravelEventEngine;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Pektiyaz\LaravelEventEngine\CanValues\EventCanResult;
use Pektiyaz\LaravelEventEngine\CanValues\EventCanValue;
use Pektiyaz\LaravelEventEngine\DoValues\EventDoResult;
use Pektiyaz\LaravelEventEngine\DoValues\EventDoValue;

class EventEngine
{
    public static function can(object $event): EventCanResult
    {
        $reason = null;
        $can = true;
        $answers = [];


        $results = Event::dispatch($event);

        foreach ($results as $listenerResult) {
            if (!$listenerResult instanceof EventCanValue) {
                throw new \RuntimeException('Invalid listener response. Listeners for "can" should return EventCanValue!');
            }
            if(!$listenerResult->can && $can){
                $can = $listenerResult->can;
                $reason = $listenerResult->reason;
            }
            $answers[] = $listenerResult;
        }


        return new EventCanResult($can, $reason, $answers);
    }

    public static function do(object $event): EventDoResult
    {
        $results = Event::dispatch($event);
        $answers = [];
        $success = true;

        foreach ($results as $listenerResult) {
            if (!$listenerResult instanceof EventDoValue) {
                throw new \RuntimeException('Invalid listener response. Listeners for "do" should return EventDoValue!');
            }
            $answers[] = $listenerResult;

            if(!$listenerResult->success) $success = false;
        }

        return new EventDoResult($success,$answers);
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
}
