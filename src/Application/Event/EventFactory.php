<?php

namespace Application\Event;

use Application\Exception\EventNotFoundException;

class EventFactory
{
    /**
     * @param string $customerTag
     *
     * @return AbstractEvent
     * @throws EventNotFoundException
     */
    public static function create($customerTag, $params)
    {
        $className = sprintf('Application\Event\Strategy\%sEvent', ucfirst($customerTag));
        if (class_exists($className)) {
            return new $className($params);
        }

        throw new EventNotFoundException(sprintf('Strategy for customer `%s` not found', $customerTag));
    }
}
