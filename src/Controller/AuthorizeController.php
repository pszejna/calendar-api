<?php

namespace Controller;

use Application\Google\CalendarService;
use Slim\Http\Request;
use Slim\Http\Response;
use Application\Google\Client;

class AuthorizeController extends AbstractController
{
	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 *
	 * @return Response
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function authorize(Request $request, Response $response, array $args)
	{
	    $this->logger->info('Request Authorize ' . json_encode($args));

		if (isset($args['email']) && !filter_var($args['email'], FILTER_VALIDATE_EMAIL)) {
			return $response->withStatus(400) ->withJson([
				'success' => false,
				'message' => 'Invalid email address'
			]);
		}

		$applicationConfig = $this->container->get('settings')->get('application');
		$client = new Client($applicationConfig['name'], $applicationConfig['credentials']);
		$client->setScopes([
			\Google_Service_Calendar::CALENDAR
		]);

		$tokenPath = $applicationConfig['tokenPath'] . md5($args['email']);

		if (!empty($request->getParam('code'))) {
			if (file_exists($tokenPath)) {
                $this->logger->info(sprintf('Already authorized: %s, %s ', $tokenPath, $request->getParam('code')));
				return $response->withStatus(200) ->withJson([
					'success' => true,
					'message' => 'Already authorized'
				]);
			}

			$client->authenticate($request->getParam('code'));
			$accessToken = $client->getAccessToken();

			if (empty($accessToken)) {
                $this->logger->error(sprintf('Unable to get access token: %s', $request->getParam('code')));
				return $response->withStatus(400) ->withJson([
					'success' => false,
					'message' => 'Unable to get access token'
				]);
			}

			$calendarService = new CalendarService($client);
			$calendarList = $calendarService->getCalendarList();
			foreach ($calendarList as $calendarId) {
                $tokenPath = $applicationConfig['tokenPath'] . md5($calendarId);
                file_put_contents($tokenPath, json_encode($accessToken));
            }

            $this->logger->info(sprintf('Authorized calendarsIds: %s', json_encode($calendarList)));

			return $response->withJson([
				'success' => true,
				'message' => 'Successful authorized'
			]);
		}

        if (file_exists($tokenPath)) {
            $token = json_decode(file_get_contents($tokenPath), true);
            if (!isset($token['refresh_token'])) {
                unlink($tokenPath);
                $this->logger->error(sprintf('Refresh token does not exists for %s', $tokenPath));
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'message' => sprintf(
                        'Refresh token does not exists. Please revoke access to application at: %s',
                        'https://myaccount.google.com/permissions'
                    )
                ]);
            }

            $client->setToken($tokenPath);
            $isAccessTokenExpired = $client->isAccessTokenExpired();
            if ($isAccessTokenExpired) {
                $client->refreshToken($client->getRefreshToken());
            }

            $calendarService = new CalendarService($client);
            $calendarList = $calendarService->getCalendarList();
            foreach ($calendarList as $calendarId) {
                $calendarTokenPath = $applicationConfig['tokenPath'] . md5($calendarId);
                if (!file_exists($calendarTokenPath)) {
                    file_put_contents($calendarTokenPath, json_encode($token));
                }
            }

            $this->logger->info(sprintf('Authorized: %s', $tokenPath));

            return $response->withJson([
                'success' => true,
                'message' => 'Authorized'
            ]);
        }

        if (!$args['email']) {
            $this->logger->warning(sprintf('Not authorized calendarId: %s', $args['email']));

            return $response->withStatus(401)->withJson([
                'success' => false,
                'message' => sprintf(
                    'Not authorized. Please visit %s to grant access',
                    $request->getUri()->getBaseUrl() .
                    $this->container->router->pathFor('authorize') . '/your@address.email'
                )
            ]);
        }

        $authUrl = $client->createAuthUrl();
        return $response->withRedirect($authUrl);
	}
}
