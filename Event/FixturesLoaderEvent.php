<?php

namespace LaFourchette\FixturesBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FixturesLoaderEvent extends Event
{
    const EVENT_BEFORE_LOAD_FIXTURES = 'fixtures.load.before';
    const EVENT_AFTER_LOAD_FIXTURES = 'fixtures.load.after';
}
