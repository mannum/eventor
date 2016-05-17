<?php

namespace Eventor;

interface ServiceActivatorInterface
{
    public function addActivator($eventName, callable $a);

    public function traverseStream(EventStreamInterface $s, EventStreamCursorInterface $c);

    public function getBus();
    public function setBus(CommandBusInterface $b);
}