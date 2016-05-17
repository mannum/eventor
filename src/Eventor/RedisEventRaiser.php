<?php

namespace Eventor;

use Predis\ClientInterface as RedisClientInterface;

class RedisEventRaiser implements EventRaiserInterface
{
    const QUEUE_NAME = 'events';

    private $redisClient;
    private $esConnection;
    private $queueName;

    public function __construct(RedisClientInterface $redisClient, EventStoreConnectionInterface $esConnection, $queueName = self::QUEUE_NAME)
    {
        $this->redisClient  = $redisClient;
        $this->queueName    = $queueName;
        $this->esConnection = $esConnection;
    }

    public function raise(EventInterface $e)
    {
        $this->redisClient->rpush($this->queueName, [$e->toJson()]);
    }

    public function publishToEventStore()
    {
        while (($eventJson = $this->redisClient->lpop($this->queueName)) !== null) {
            $event = Event::fromJson($eventJson);

            try {
                $this->esConnection->writeEvent($event);
            } catch (\InvalidArgumentException $e) {
                // @todo log exception here
                // just skip, do nothing here, maybe push into invalid events queue
                continue;
            } catch (\Exception $e) {
                // @todo and another exception
                // we have a fatal error, lets insert the event back into the queue and exit
                $this->redisClient->lpush($this->queueName, [$event->toJson()]);
                break;
            }
        }
    }
}