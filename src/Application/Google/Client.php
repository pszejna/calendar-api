<?php

namespace Application\Google;

use Application\Exception\TokenNotFoundException;

class Client extends \Google_Client
{
	/**
	 * Client constructor.
	 *
	 * @param string $applicationName
	 * @param string $credentialFile
	 */
	public function __construct( $applicationName, $credentialFile ) {
		parent::__construct();

		$this->setApplicationName($applicationName);
		$this->setAuthConfigFile($credentialFile);
		$this->setAccessType('offline');
	}

	public function setToken($tokenFile)
	{
		if (!file_exists($tokenFile)) {
			throw new TokenNotFoundException('Token not found');
		}

		$tokenArray = json_decode(file_get_contents($tokenFile), true);

		//set the token
		$token = $tokenArray['access_token'];
		$this->setAccessToken($token);
		$this->refreshToken($tokenArray['refresh_token']);
	}
}