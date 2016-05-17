<?php

namespace Eventor;


interface EventStoreConnectionInterface
{
    public function getUrl();
    public function getStreamUrl($s);
    public function getStreamForwardUrl($stream, $startPosition, $pageSize);

    public function readEvents($url);
    public function writeEvent(EventInterface $e);
}