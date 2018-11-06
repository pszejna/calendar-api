<?php

namespace Application\Google;

use Google_Client;

class CalendarService extends \Google_Service_Calendar
{
	public function __construct( Google_Client $client ) {
		parent::__construct( $client );
	}

	public function getCalendarList($roles = ['owner', 'writer'])
    {
        return array_map(function (\Google_Service_Calendar_CalendarListEntry $calendar) {
            return $calendar->getId();
        }, array_filter(
            (array) $this->calendarList->listCalendarList()->getItems(),
            function(\Google_Service_Calendar_CalendarListEntry $calendar) use ($roles) {
                return in_array($calendar->getAccessRole(), $roles);
            }
        ));
    }
}
