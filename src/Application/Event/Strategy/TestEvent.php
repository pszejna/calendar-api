<?php

namespace Application\Event\Strategy;

use Application\Event\AbstractEvent;

class TestEvent extends AbstractEvent
{
    protected $id;
    protected $description;
    protected $title;
    protected $location;
    protected $start;
    protected $stop;
    protected $isDeleted = false;

    public function __construct(array $data)
    {
        throw new \LogicException('Cannot use test event');

        $this->id = $this->generateId('test');
        $this->description = 'Test description';
        $this->title = 'Tytół';
        $this->location = 'ul. Zgierska 21';
        $this->start = '2018-11-03 10:00';
        $this->stop = '2018-11-03 11:00';
        $this->isDeleted = false;
    }

    public function isDeleted()
    {
        return $this->isDeleted === true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getStop()
    {
        return $this->stop;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
