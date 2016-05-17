<?php
namespace Eventor;

class Event implements EventInterface
{
    protected $data = [];
    protected $streamName;
    protected $id;
    protected $type;

    public function __construct($type, $streamName, $data = [], $id = null)
    {
        $this->type       = $type;
        $this->streamName = $streamName;
        $this->id         = $id;

        if ( ! $this->id) {
            $this->id = $this->uuid();
        }

        $this->setData($data);
    }

    public function getStreamName()
    {
        return $this->streamName;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $d)
    {
        $this->data = $d;
    }

    public function getType()
    {
        return $this->type;
    }

    public function toJson()
    {
        return json_encode([
            'eventType'  => $this->type,
            'streamName' => $this->streamName,
            'eventId'    => $this->id,
            'data'       => $this->data
        ]);
    }

    public static function fromJson($j)
    {
        $e = json_decode($j, true);

        if (empty($e['eventType']) || empty($e['streamName']) || empty($e['eventId']) || empty($e['data'])) {
            throw new \InvalidArgumentException(sprintf('Invalid JSON supplied, unable to convert to event, data supplied [%s]', $e));
        }

        return new self($e['eventType'], $e['streamName'], $e['data'], $e['eventId']);
    }

    private function uuid()
    {
        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}