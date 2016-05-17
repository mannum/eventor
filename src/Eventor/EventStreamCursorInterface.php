<?php
namespace Eventor;


interface EventStreamCursorInterface
{
    public function fetch($stream);
    public function increment($stream);
}