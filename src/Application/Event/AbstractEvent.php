<?php

namespace Application\Event;

abstract class AbstractEvent implements EventInterface
{
	protected $id;

	protected function generateId($hash)
	{
		return str_replace(['w', 'x', 'y', 'z'], '', mb_strtolower(md5($hash)));
	}

	/**
	 * @return \Google_Service_Calendar_Event
	 */
	public function prepare()
	{
		$data = [
			'summary' => $this->getTitle(),
			'location' => $this->getLocation(),
			'description' => $this->getDescription(),
			'start' => array(
				'dateTime' => date(\DateTime::RFC3339, strtotime($this->getStart())),
				'timeZone' => 'Europe/Warsaw',
			),
			'id' => $this->getId(),
			'end' => array(
				'dateTime' => date(\DateTime::RFC3339, strtotime($this->getStop())),
				'timeZone' => 'Europe/Warsaw',
			)
		];

		return new \Google_Service_Calendar_Event($data);
	}
}