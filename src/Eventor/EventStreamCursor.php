<?php

namespace Eventor;

use Predis\ClientInterface;

class EventStreamCursor implements EventStreamCursorInterface
{
    protected $redis;
    protected $id;

    const HASH_NAME = 'eventstream_cursors';

    public function __construct($id, ClientInterface $redis)
    {
        $this->redis = $redis;
        $this->id    = $id;
    }

    public function fetch()
    {
        return max($this->redis->hget(self::HASH_NAME, $this->id), -1);
    }

    public function increment()
    {
        return $this->redis->hincrby(self::HASH_NAME, $this->id, 1);
    }
}