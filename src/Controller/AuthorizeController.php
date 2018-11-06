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
				return $response->withStatus(200) ->withJson([
					'success' => true,
					'message' => 'Already authorized'
				]);
			}

			$client->authenticate($_GET['code']);
			$accessToken = $client->getAccessToken();

			if (empty($accessToken)) {
				return $response->withStatus(400) ->withJson([
					'success' => false,
					'message' => 'Unable to get access token'
				]);
			}

			$calendarService = new CalendarService($client);
			foreach ($calendarService->getCalendarList() as $calendarId) {
                $tokenPath = $applicationConfig['tokenPath'] . md5($calendarId);
                file_put_contents($tokenPath, json_encode($accessToken));
            }

			return $response->withJson([
				'success' => true,
				'message' => 'Successful authorized'
			]);
		}

        if (file_exists($tokenPath)) {
            $token = json_decode(file_get_contents($tokenPath), true);
            if (!isset($token['refresh_token'])) {
                unlink($tokenPath);
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'message' => sprintf(
                        'Refresh token does not exists. Please revoke access to application at: %s',
                        'https://myaccount.google.com/permissions'
                    )
                ]);
            }

            return $response->withJson([
                'success' => true,
                'message' => 'Authorized'
            ]);
        }

        if (!$args['email']) {
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
