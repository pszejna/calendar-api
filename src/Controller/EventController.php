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
        $this->logger->info(sprintf(
            'Request addEvent, data: %s, content: %s',
            json_encode($args),
            json_encode($request->getParsedBody())
        ));

		$applicationConfig = $this->container->get('settings')->get('application');
		$client = new Client($applicationConfig['name'], $applicationConfig['credentials']);
		$client->setScopes([
			\Google_Service_Calendar::CALENDAR
		]);

		$customerTag = $args['tag'];
		$calendarId = $args['email'];
		$tokenPath = $applicationConfig['tokenPath'] . md5($calendarId);

		if (!file_exists($tokenPath)) {
		    $this->logger->warning(sprintf('Not authorized %s', $calendarId));

			return $response->withStatus(401)->withJson([
				'success' => false,
				'message' => 'Unauthorized. Please visit ' .
                     $request->getUri()->getBaseUrl() .
                     $this->container->router->pathFor('authorize') . '/' . $calendarId
			]);
		}

        try {
            // setup clients
            $client->setToken($tokenPath);
            $isAccessTokenExpired = $client->isAccessTokenExpired();
            if ($isAccessTokenExpired) {
                $client->refreshToken($client->getRefreshToken());
            }

            $event = EventFactory::create($customerTag, $request->getParams());

            $calendar = new CalendarService($client);
            if ($event->isDeleted()) {
                $this->logger->info(sprintf('Try to delete event %s', json_encode([
                    'calendarId' => $calendarId,
                    'id' => $event->getId()
                ])));

                $calendar->events->delete(
                    $calendarId,
                    $event->getId()
                );
            } else {
                $this->logger->info(sprintf('Try to insert event %s', json_encode([
                    'calendarId' => $calendarId,
                    'data' => [
                        'id' => $event->getId(),
                        'title' => $event->getTitle(),
                        'description' => $event->getDescription(),
                        'location' => $event->getLocation(),
                        'start' => $event->getStart(),
                        'stop' => $event->getStop()
                    ]
                ])));

                $calendar->events->insert(
                    $calendarId,
                    $event->prepare()
                );
            }

            $this->logger->info('Event ' . ($event->isDeleted() ? 'deleted' : 'added'));

            return $response->withJson([
                'success' => true,
                'message' => 'Event ' . ($event->isDeleted() ? 'deleted' : 'added')
            ]);
        } catch (EventNotFoundException $exception) {
            return $response->withStatus(400)->withJson([
                'success' => false,
                'mesage' => $exception->getMessage()
            ]);
        } catch (\Google_Exception $exception) {
		    $this->logger->warning('Cannot insert or delete event');

            if ( $calendar && !$event->isDeleted()) {
                try {
                    $this->logger->info(sprintf('Try to update event %s', json_encode([
                        'calendarId' => $calendarId,
                        'data' => [
                            'id' => $event->getId(),
                            'title' => $event->getTitle(),
                            'description' => $event->getDescription(),
                            'location' => $event->getLocation(),
                            'start' => $event->getStart(),
                            'stop' => $event->getStop()
                        ]
                    ])));

                    $calendar->events->update(
                        $calendarId,
                        $event->getId(),
                        $event->prepare()
                    );

                    $this->logger->info('Event updated');

                    return $response->withJson( [
                        'success' => true,
                        'message' => 'Event updated'
                    ] );
                } catch (\Google_Service_Exception $exception) {
                    $this->logger->error($exception->getMessage());

                    return $response->withStatus(500)->withJson( [
                        'success' => false,
                        'message' => 'Calendar not found'
                    ] );
                }
            }

            $this->logger->error($exception->getMessage());

            return $response->withStatus(500)->withJson( [
                'success' => false,
                'message' => $exception->getMessage()
            ] );
        } catch (\Google_Service_Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $response->withStatus(500)->withJson( [
                'success' => false,
                'message' => 'Calendar not found'
            ] );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

	}
}
