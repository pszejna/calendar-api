<?php

namespace Application\Google;

use Google_Client;

class CalendarService extends \Google_Service_Calendar
{
	public function __construct( Google_Client $client ) {
		parent::__construct( $client );
	}
}
