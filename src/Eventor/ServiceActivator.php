<?php
namespace Eventor;


class ServiceActivator implements ServiceActivatorInterface
{
    protected $activators = [];
    protected $bus;

    public function addActivator($eventName, callable $a)
    {
        $this->activators[$eventName] = $a;
    }

    public function traverseStream(EventStreamInterface $s, EventStreamCursorInterface $cursor)
    {
        foreach ($s as $event) {
            if (empty($this->activators[$event->getType()])) continue;

            try {
                $command = $this->activators[$event->getType()]($event->getData());
                $this->bus->handle($command);
            } catch (\Exception $e) {
                // @todo do something with exceptions here
                var_dump($e->getMessage());
            }

            $cursor->increment();
        }
    }

    public function getBus()
    {
        return $this->bus;
    }

    public function setBus(CommandBusInterface $b)
    {
        $this->bus = $b;
    }
}