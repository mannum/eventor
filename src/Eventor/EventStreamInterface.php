<?php

namespace Eventor;


interface EventStreamInterface extends \Iterator
{
    public function getName();
}