<?php
namespace Eventor;


class CommandBus implements CommandBusInterface
{
    protected $commandHandlers = [];

    public function addCommandHandler($messageName, callable $handler)
    {
        $this->commandHandlers[$messageName] = $handler;
    }

    public function handle($message)
    {
        if (empty($message::$name)) return;

        if (empty($this->commandHandlers[$message::$name])) return;

        $this->commandHandlers[$message::$name]($message);
    }
}