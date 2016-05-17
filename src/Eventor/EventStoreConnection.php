<?php
namespace Eventor;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Middleware;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;

class EventStoreConnection implements EventStoreConnectionInterface
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getStreamUrl($s)
    {
        return sprintf('%s/streams/%s', $this->url, $s);
    }

    public function getStreamForwardUrl($stream, $startPosition, $pageSize)
    {
        return sprintf('%s/%s/forward/%s', $this->getStreamUrl($stream), $startPosition, $pageSize);
    }

    public function readEvents($url)
    {
        $client = $this->createHttpClient();

        $response = $client->get($url, [
            'headers' => [
                'Accept' => 'application/vnd.eventstore.atom+json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function writeEvent(EventInterface $e)
    {
        $client = $this->createHttpClient();

        $client->post($this->getStreamUrl($e->getStreamName()), [
            'headers' => [
                'Content-Type'       => 'application/json',
                'ES-ExpectedVersion' => '-2',
                'ES-EventType'       => $e->getType(),
                'ES-EventId'         => $e->getId()
            ],
            'body' => json_encode($e->getData())
        ]);
    }

    private function createHttpClient()
    {
        $stack = HandlerStack::create(new CurlHandler());
        $stack->push(Middleware::retry($this->createRetryHandler(), function($retries) {
            return 1000 * intval(pow(2, $retries - 1));
        }));
        return new HttpClient([
            'handler' => $stack,
        ]);
    }

    private function createRetryHandler()
    {
        return function (
            $retries,
            Psr7Request $request,
            Psr7Response $response = null,
            RequestException $exception = null
        ) {
            if ($retries >= 3) {
                return false;
            }

            if ( ! ($this->isServerError($response) || $this->isConnectError($exception))) {
                return false;
            }

            return true;
        };
    }

    private function isServerError(Psr7Response $response = null)
    {
        return $response && $response->getStatusCode() >= 500;
    }

    private function isConnectError(RequestException $exception = null)
    {
        return $exception instanceof ConnectException;
    }
}