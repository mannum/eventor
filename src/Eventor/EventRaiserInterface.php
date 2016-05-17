<?php

namespace Eventor;

interface EventRaiserInterface
{
    public function raise(EventInterface $e);
}