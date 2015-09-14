<?php

namespace LaFourchette\FixturesBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;

class FixturesEventDispatcher extends EventDispatcher
{
    const SUBSCRIBE_TAG = 'fixtures.event_subscriber';
}
