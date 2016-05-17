<?php

namespace Eventor;

class EventStream implements EventStreamInterface
{
    protected $es;
    protected $name;
    protected $events = [];
    protected $lastProcessedEventId;
    protected $batchSize;
    protected $pageSize;
    protected $numProcessedEntries;
    protected $nextPageUrl;

    public function __construct(EventStoreConnectionInterface $es, $name, $lastProcessedEventId = null, $batchSize = null, $pageSize = 100)
    {
        $this->es                   = $es;
        $this->name                 = trim($name, " \t\n\r\0\x0B/");
        $this->lastProcessedEventId = is_null($lastProcessedEventId) ? -1 : (int) $lastProcessedEventId;
        $this->batchSize            = (int) $batchSize;
        $this->pageSize             = max((int) $pageSize, 1); // we request from ES at last one event per page request
    }

    public function getName()
    {
        return $this->name;
    }

    public function current()
    {
        $this->numProcessedEntries++;

        return current($this->events);
    }

    public function next()
    {
        return next($this->events);
    }

    public function key()
    {
        return key($this->events);
    }

    public function valid()
    {
        // do we have more entries? lets find out, if not lets try previous page
        $navigateToPreviousPage = key($this->events) === null;
        $reachedBatchSizeLimit  = ($this->batchSize && $this->numProcessedEntries === $this->batchSize);

        if ($reachedBatchSizeLimit) return;

        if ($navigateToPreviousPage && $this->nextPageUrl) {
            $this->fetchEvents($this->nextPageUrl);
        }

        return key($this->events) !== null;
    }

    public function rewind()
    {
        $this->numProcessedEntries = 0;
        $this->nextPageUrl         = null;

        // we should open the feed here and get the first entries
        $this->fetchEvents($this->es->getStreamForwardUrl($this->name, $this->lastProcessedEventId + 1, $this->pageSize));
        
        return reset($this->events);
    }

    private function fetchEvents($url)
    {
        $url .= '?embed=body';

        $feed = $this->es->readEvents($url);

        if (empty($feed)) return;

        $this->extractNextPageUrl($feed);

        $this->events = [];

        foreach (array_reverse($feed['entries']) as $entry) {
            $this->events[] = new Event(
                $entry['eventType'],
                $this->name,
                json_decode($entry['data'], true),
                $entry['eventId']
            );
        }
    }

    private function extractNextPageUrl($feed)
    {
        $this->nextPageUrl = null;

        if (empty($feed['links'])) return;

        foreach ($feed['links'] as $link) {
            if (empty($link['uri']) || empty($link['relation'])) continue;

            if ($link['relation'] === 'previous') {
                $this->nextPageUrl = $link['uri'];
                break;
            }
        }
    }
}