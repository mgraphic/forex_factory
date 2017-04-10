<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
	/**
	 * Default welcome page
	 * 
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request)
	{
		// replace this example code with whatever you need
		return $this->render('default/index.html.twig', [
			'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
		]);
	}
	
	/**
	 * Find events by passing optional parameters
	 * 
	 * @Route(
	 *     "/events",
	 *     name="events_find"
	 * )
	 */
	public function findAction(Request $request)
	{
		$queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();
		
		$queryBuilder
			->select('e')
			->from('event', 'e')
			->where('1=1');
		
		if ($request->query->get('title')) {
			$this->doExpression($queryBuilder, 'e.title', 'like', $request->query->get('title'));
		}
		
		if ($request->query->get('date')) {
			$datetime = new \DateTime($request->query->get('date'));
			$this->doExpression($queryBuilder, 'e.date', '=', $datetime->format('Y-m-d H:i:s'));
		}
		
		if ($request->query->get('impact')) {
			$expression = $this->evalNumericExpression($request->query->get('impact'));
			$this->doExpression($queryBuilder, 'e.impact', $expression['expression'], (int) $expression['value']);
		}
		
		if ($request->query->get('instrument')) {
			$this->doExpression($queryBuilder, 'e.instrument', 'like', $request->query->get('instrument'));
		}
		
		if ($request->query->get('actual')) {
			$this->doExpression($queryBuilder, 'e.actual', '=', (float) $request->query->get('actual'));
		}
		
		if ($request->query->get('forecast')) {
			$this->doExpression($queryBuilder, 'e.forecast', '=', (float) $request->query->get('forecast'));
		}
		
		$results = $queryBuilder->getQuery()->getArrayResult();
		
		if (empty($results)) {
			return new Response(
				$this->getJsonError('No events found with matched parameters')
			);
		}
		
		$response = new Response;
		$response->setContent($this->getJson($results));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	/**
	 * Get event info by Event ID
	 * 
	 * @Route(
	 *     "/events/{event_id}",
	 *     name="events_info",
	 *     requirements={
	 *         "event_id": "\d+"
	 *     }
	 * )
	 * @Method({"GET"})
	 */
	public function infoAction(Request $request, $event_id)
	{
		$event = $this->getDoctrine()
			->getRepository('AppBundle:Event')
			->find($event_id);
		
		if (!$event) {
			return new Response(
				$this->getJsonError("No event found for id $event_id")
			);
		}
		
		$result = $this->formatEvent($event);
		
		$response = new Response;
		$response->setContent($this->getJson($result));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	/**
	 * Edit event by passing optional POST data and Event ID
	 * 
	 * @Route(
	 *     "/events/{event_id}",
	 *     name="events_edit",
	 *     requirements={
	 *         "event_id": "\d+"
	 *     }
	 * )
	 * @Method({"POST"})
	 */
	public function editAction(Request $request, $event_id)
	{
		$event = $this->getDoctrine()
			->getRepository('AppBundle:Event')
			->find($event_id);
		
		if (!$event) {
			$response = new Response;
			$response->setContent(
				$this->getJsonError("No event found for id $event_id")
			);
			$response->headers->set('Content-Type', 'application/json');
			
			return $response;
		}
		
		$edited = false;
		
		if ($request->request->get('title') AND $request->request->get('title') != $event->getTitle()) {
			$event->setTitle($request->request->get('title'));
			$edited = true;
		}
		
		if ($request->request->get('date') AND new \DateTime($request->request->get('date')) != $event->getDate()) {
			$event->setDate(new \DateTime($request->request->get('date')));
			$edited = true;
		}
		
		if ($request->request->get('impact') AND (int) $request->request->get('impact') != $event->getImpact()) {
			$event->setImpact((int) $request->request->get('impact'));
			$edited = true;
		}
		
		if ($request->request->get('instrument') AND $request->request->get('instrument') != $event->getInstrument()) {
			$event->setInstrument($request->request->get('instrument'));
			$edited = true;
		}
		
		if ($request->request->get('actual') AND (float) $request->request->get('actual') != $event->getActual()) {
			$event->setActual((float) $request->request->get('actual'));
			$edited = true;
		}
		
		if ($request->request->get('forecast') AND (float) $request->request->get('forecast') != $event->getForecast()) {
			$event->setForecast((float) $request->request->get('forecast'));
			$edited = true;
		}
		
		if (!$edited) {
			return new Response(
				$this->getJsonError('Unable to edit (nothing to update) the event with id ' . $event->getId())
			);
		}
		
		// get the entitiy manager
		$em = $this->getDoctrine()->getManager();
		
		// tells Doctrine you want to (eventually) update the Event (no query yet)
		$em->persist($event);
		
		// actually executes the query (i.e. the UPDATE query)
		$em->flush();
		
		$response = new Response;
		$response->setContent(
			$this->getJson(['message' => 'Updated the event with id ' . $event->getId()])
		);
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	/**
	 * Evaluate string for expression and numeric value
	 *
	 * @param string $string
	 * @return array[]
	 */
	protected function evalNumericExpression($string)
	{
		$string = str_replace(' ', '', $string);
		
		preg_match('/^(.*?(?=\d))/', $string, $exp);
		preg_match('/(\d+)$/', $string, $num);
		
		return [
			'expression' => $exp[1][0],
			'value' => $num[1][0]
		];
	}
	
	/**
	 * Add a conditional expression on QueryBuilder object
	 *
	 * @param Doctrine\DBAL\Query\QueryBuilder $qb
	 * @param string $field
	 * @param string $expression
	 * @param mixed $value
	 * @param string $junction
	 * @return Doctrine\ORM\Query\Expr
	 */
	protected function doExpression(Doctrine\DBAL\Query\QueryBuilder $qb, $field, $expression, $value, $junction = 'and')
	{
		switch (strtolower($expression)) {
			case 'like':
				$expression = 'LIKE';
				break;
			case 'lt':
			case '<':
				$expression = '<';
				break;
			case 'lte':
			case '<=':
				$expression = '<=';
				break;
			case 'gt':
			case '>':
				$expression = '>';
				break;
			case 'gte':
			case '>=':
				$expression = '>=';
				break;
			case 'neq':
			case '!=':
			case '<>':
				$expression = '!=';
				break;
			case 'eq':
			case '=':
			default:
				$expression = '=';
				break;
		}
		
		$where = "{$field} {$expression} ?";
		
		if ($junction == 'or') {
			return $qb->orWhere($where, $value);
		} else {
			return $qb->andWhere($where, $value);
		}
	}
	
	/**
	 * Convert and format an Event object into an array
	 *
	 * @param \AppBundle\Entity\Event $event
	 * @return array
	 */
	protected function formatEvent(Event $event)
	{
		$array = [
			'id' => $event->getId(),
			'title' => $event->getTitle(),
			'date' => $event->getDate()->format('c'),
			'impact' => $event->getImpact(),
			'instrument' => $event->getInstrument(),
			'actual' => $event->getActual(),
			'forecast' => $event->getForecast()
		];
		
		return $array;
	}
	
	/**
	 * Get JSON formatted string of result array
	 * 
	 * @param array $array
	 * @return string
	 */
	protected function getJson(array $array)
	{
		return json_encode($array);
	}
	
	/**
	 * Get JSON formatted string with error an message
	 * 
	 * @param string $message
	 * @return string
	 */
	protected function getJsonError($message)
	{
		return $this->getJson(['error' => $message]);
	}
	
	/**
	 * @return \AppBundle\Entity\Event
	 */
	protected function getEventEntityObject()
	{
		return new Event;
	}
}

