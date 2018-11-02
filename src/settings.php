<?php

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'calendar-api',
            'path' =>  '../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

	    'application' => [
	    	'name' => 'Calendar Api Test 1.0',
	    	'credentials' => '../config/credentials.json',
		    'token' =>  '../config/token.json',
		    'tokenPath' => '../config/tokens/'
	    ]
    ]
];
