<?php
namespace Eventor;


interface EventStreamCursorInterface
{
    public function fetch();
    public function increment();
}