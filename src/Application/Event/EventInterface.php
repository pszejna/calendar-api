<?php

namespace Application\Event;

interface EventInterface
{
	/**
	 * EventInterface constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data);
	public function getId();
	public function getStart();
	public function getStop();
	public function getTitle();
	public function getLocation();
	public function getDescription();
}
