Linxio Notifications System
========================


#### Run consumers before start

Consumer for handling events:
````
nohup php bin/console rabbitmq:consumer events &

````

Consumer for handling sms messages:
````
nohup php bin/console rabbitmq:consumer sms &

````

Consumer for handling email messages:
````
nohup php bin/console rabbitmq:consumer email &

````

Consumer for handling web app messages:
````
nohup php bin/console rabbitmq:consumer webapp &

````

Consumer for handling mobile app messages:
````
nohup php bin/console rabbitmq:consumer mobileapp &

````


###Step 1. The event trigger and processing

#####1.1 The first way for a trigger event

Use service `App\Service\Notification\EventDispatcher`
````
$dispatcher->dispatch('event_name', $entity);

//$dispatcher is instance App\Service\Notification\EventDispatcher
````

####1.2 The second way for a trigger event

Use service `Symfony\Component\EventDispatcher\EventDispatcherInterface`

````
use App\Events\Notification\NotificationEvent;

$dispatcher->dispatch(NotificationEvent::NAME, new NotificationEvent('event_name', $entity));

//$dispatcher is instance Symfony\Component\EventDispatcher\EventDispatcherInterface
````


### 2. The event listener catches this event

2.1. Event listener `App\EventListener\Notification\NotificationListener` catches this event.

2.2. Searching for an event in the database by the alias

````
Note: 
There may be 2 events with one alias but different types 'user' and 'system'

Important:
Users do not see system type events
````
2.3. Every received event is entity validated.
2.4. Messages are sent to the event queue. `name=events`

Message format:
````
{
	"event_id": {event_id},
	"entity_id": {entity_id},
	"dt": {datetime|'Y-m-d H:i:s'}
}
````

###Step 2. The event processing. And creating notifications messages

3.1. Handling event with `App\Service\Notification\Queue\Consumer\NotificationEventConsumer`

````
bin/console rabbitmq:consumer events
````

3.2. Collect Notifications with service `App\Service\Notification\NotificationCollectorService`

3.2.1. Generate placeholders.

We using 4 default placeholders for each event in db. The placeholders replace values in templates.

````
Example:
Event `USER_CREATED` and Type `system`

[
    'data_short' => <value>,
    'data_long' => <value>,
    'data_details' => <value>,
    'data_url' => <value>,
]

````

`For add placeholder for the event add anonymous function to the array`


3.2.2. Get notifications by type and listener team.
````
Note: The Listener Team its the entity owner.
````

3.2.3. Filter notifications by scopes.

Each notification contains at least one scope (now only one).

Each scope contains a scope type and value.

`For add new scope handler add anonymous function to the array`

3.2.3. Get Recipients.
3.2.4. Create message gor each recipient and transport. 


###Step 3.  Processing messages

Run command:
````
bin/console rabbitmq:consumer app:notifications:send
````

This command getting messages for send. And publishing to unique for transport queue.