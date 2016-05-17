<?php
namespace Eventor;


interface CommandBusInterface
{
    /**
     * @param object $message
     * @return void
     */
    public function handle($message);
}