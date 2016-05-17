<?php

namespace Eventor;

interface EventInterface    
{
    public function getStreamName();
    public function getId();

    public function getData();
    public function setData(array $d);

    public function getType();

    public function toJson();

    public static function fromJson($j);
}