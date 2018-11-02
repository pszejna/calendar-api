<?php

namespace Controller;

use Application\Event\EventFactory;
use Application\Exception\EventNotFoundException;
use Application\Google\CalendarService;
use Slim\Http\Request;
use Slim\Http\Response;
use Application\Google\Client;

class EventController extends AbstractController
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
	public function addEvent(Request $request, Response $response, array $args)
	{
		$applicationConfig = $this->container->get('settings')->get('application');
		$client = new Client($applicationConfig['name'], $applicationConfig['credentials']);
		$client->setScopes([
			\Google_Service_Calendar::CALENDAR
		]);

		$customerTag = $args['tag'];
		$calendarId = $args['email'];
		$tokenPath = $applicationConfig['tokenPath'] . md5($calendarId);

		if (!file_exists($tokenPath)) {
			return $response->withStatus(401)->withJson([
				'success' => false,
				'message' => 'Unauthorized. Please visit ' .
				             $request->getUri()->getBaseUrl() .
				             $this->container->router->pathFor('authorize') . '/' . $calendarId
			]);
		} else {
			try {
				// setup clients
				$client->setToken($tokenPath);
				$isAccessTokenExpired = $client->isAccessTokenExpired();
				if ($isAccessTokenExpired) {
					$client->refreshToken($client->getRefreshToken());
				}

				$event = EventFactory::create($customerTag, $request->getParams());

				$calendar = new CalendarService($client);
				$calendar->events->insert(
					$calendarId,
					$event->prepare()
				);

				return $response->withJson([
					'success' => true,
					'message' => 'Event added'
				]);
			} catch (EventNotFoundException $exception) {
				return $response->withStatus(400)->withJson([
					'success' => false,
					'mesage' => $exception->getMessage()
				]);
			} catch (\Google_Exception $exception) {
				if ( $calendar ) {
					try {
						$calendar->events->update(
							$calendarId,
							$event->getId(),
							$event->prepare()
						);
						return $response->withJson( [
							'success' => true,
							'message' => 'Event updated'
						] );
					} catch (\Google_Service_Exception $exception) {
						return $response->withStatus(500)->withJson( [
							'success' => false,
							'message' => 'Calendar not found'
						] );
					}
				}

				return $response->withStatus(500)->withJson( [
					'success' => false,
					'message' => $exception->getMessage()
				] );
			} catch (\Google_Service_Exception $exception) {
				return $response->withStatus(500)->withJson( [
					'success' => false,
					'message' => 'Calendar not found'
				] );
			} catch (\Exception $exception) {
				return $response->withStatus(500)->withJson([
					'success' => false,
					'message' => $exception->getMessage()
				]);
			}
		}
	}
}