<?php

namespace Application\Event\Strategy;

use Application\Event\AbstractEvent;

class DthEvent extends AbstractEvent
{
    protected $id;
    protected $description;
    protected $title;
    protected $location;
    protected $start;
    protected $stop;
    protected $isDeleted = false;

    private $requiredFields = [
        'name',
        'vorname',
        'mitgliedsnummer',
        'start',
        'end',
        'description'
    ];

    public function __construct(array $data)
    {
        if (!$data['eventName'] === 'RecordSummarized') {
            throw new \InvalidArgumentException('Invalid event');
        }

        $values = json_decode($data['data']['values'], true);
        foreach ($this->requiredFields as $field) {
            if (!array_key_exists($field, $values)) {
                throw new \InvalidArgumentException("Missing field {$field}");
            }
        }

        $this->id = $this->generateId($data['fccInstance'] . $data['data']['recordsId']);
        $this->title = sprintf(
            '%s %s %s %s',
            $values['name'],
            $values['vorname'],
            $data['data']['phoneNumber'],
            $values['mitgliedsnummer']
        );

        $this->description = $values['description'];
        $this->start = $values['start'];
        $this->stop = $values['end'];
        $this->isDeleted = empty($this->start) || empty($this->stop);
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
