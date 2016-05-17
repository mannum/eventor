<?php

namespace Eventor;

use Predis\ClientInterface;

class EventStreamCursor implements EventStreamCursorInterface
{
    protected $redis;

    const HASH_NAME = 'eventstream_cursors';

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function fetch($stream)
    {
        return max($this->redis->hget(self::HASH_NAME, $stream), -1);
    }

    public function increment($stream)
    {
        return $this->redis->hincrby(self::HASH_NAME, $stream, 1);
    }
}