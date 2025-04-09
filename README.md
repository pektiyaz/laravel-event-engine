# EventEngine for Laravel
EventEngine is a powerful and declarative extension for Laravel’s event system, providing a clear can → do → after pattern for modular business logic.

# Installation
```shell
composer require pektiyaz/laravel-event-engine
```

# Features
## Method	Description
- can()	Checks if the action is allowed before proceeding
- do()	Performs an action and expects a success/failure response
- doAtomic()	Performs an action inside a DB transaction (rollback-safe)
- after()	Fires events that don’t affect control flow
# Listener Contracts

## can(...) listeners:
Must return:
```php
return new EventCanValue(true)
// OR
return new EventCanValue(true, $data)
// OR
return new EventCanValue(false, "Reason..")
```

## do(...) listeners:
Must return:

```php
return new EventDoValue(true)
// OR
return new EventDoValue(true, $data) 
// OR
return new EventDoValue(false, "Reason...")

```
## after(...) listeners:
Should not return anything. These may implement ShouldQueue.

# Setup
Create an event and a listener for can(), do(), or after() patterns.

Register the listener in Laravel (EventServiceProvider).

## 1. Create an Event
Example: CanClientPay
```php
namespace App\Events;

class CanClientPay
{
    public function __construct(
        public int   $clientId,
        public float $amount
    ) {}
}
```

##  2. Create a Listener
Listener that handles this event and returns a EventCanValue response.

```php
namespace App\Listeners;

use App\Events\CanClientPay;use Pektiyaz\LaravelEventEngine\CanValues\EventCanValue;

class CheckClientBalanceListener
{
    public function handle(CanClientPay $event): EventCanValue
    {
        // Example logic: client can pay if they have more than $event->amount
        $balance = 500; // You’d normally fetch this from DB
    
        if ($balance >= $event->amount) {
            return new EventCanValue(true);
        }
    
        return new EventCanValue(false, 'Insufficient balance');
    }
}
```
Note: Always return an array from the listener (for can() and do() usage).

## 3. Register the Listener
Open app/Providers/EventServiceProvider.php and add your event + listener:

```php
protected $listen = [
    \App\Events\CanClientPay::class => [
        \App\Listeners\CheckClientBalanceListener::class,
    ],
];
```
Then run:

```shell
php artisan event:clear
php artisan event:cache
```

# Usage Examples

##  can() — check permission
```php
use App\Support\EventEngine;
use App\Events\CanClientPay;

$result = EventEngine::can(new CanClientPay($clientId, $amount));

if (!$result->can) {
    // Listing all answers
    foreach ($result->answers as $answer) {
        //$answer->can
        //$answer->data
        //$answer->toArray()
        Log::debug('Event Answer', $answer->toArray());
    }
    //$result->toArray()
    return response()->json(['error' => 'Payment denied', 'details' => $result->data]);
}
```
##  do() — execute an action
```php
use App\Events\ClientProcessPay;

$process = EventEngine::do(new ClientProcessPay($clientId, $amount));

if (!$process->success) {
    // Listing all answers
    foreach ($result->answers as $answer) {
        //$answer->success
        //$answer->data
        //$answer->toArray()
        Log::debug('Event Answer', $answer->toArray());
    }
    //$result->toArray()
    return response()->json(['error' => 'Payment failed', 'details' => $process->data]);
}

return response()->json(['message' => 'Payment success', 'data' => $process->data]);
```
## doAtomic() — run EventEngine::do in DB transaction

```php
$response = EventEngine::doAtomic(new ClientPaid($clientId, $amount));

if (!$response->success) {
    return response()->json([
        'error' => 'Transaction rolled back',
        'details' => $response->data,
    ]);
}
```
##  after() — fire an event without expecting a response
```php
use App\Events\ClientPaid;

EventEngine::after(new ClientPaid($clientId, $amount));
```

# With Listener name

```php
namespace App\Listeners;

use App\Events\CanClientPay;use Pektiyaz\LaravelEventEngine\CanValues\EventCanValue;

class CanClientPayListener
{
    public function handle(CanClientPay $event): EventCanValue
    {
        // Example logic: client can pay if they have more than $event->amount
        $balance = 500; // You’d normally fetch this from DB
    
        if ($balance >= $event->amount) {
            return new EventCanValue(true);
        }
    
        return new EventCanValue(false, 'Insufficient balance', "CanClientPayListener");
    }
}
```

```php
$result = EventEngine::can(new CanClientPay($clientId, $amount));
foreach($result->answers as $answer){
    $answer->listener // CanClientPayListener
}
```

# Why use EventEngine?
- Clear separation of responsibilities (can, do, after)

- Easy to test and maintain

- Decouples business logic from the framework

- Centralized event orchestration

- Scales well with modular systems

# Coming Soon (ideas)
- Configurable behavior via config file

- failFast and collectAllErrors options

- Automatic event registration

- Publishable Laravel package with service provider