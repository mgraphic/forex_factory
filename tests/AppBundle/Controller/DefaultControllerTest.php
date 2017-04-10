<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
	public function testIndex()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
	}
	
	public function testInfo()
	{
		// Create event
		$id = $this->createEvent();
		
		// Get client and response
		$client->request('GET', "/events/{$id}");
		$response = $client->getResponse();
		
		// Test if response is OK
		$this->assertSame(200, $response->getStatusCode());
		
		// Test if Content-Type is valid application/json
		$this->assertSame('application/json', $response->headers->get('Content-Type'));
		
		// Test if title was inserted
		$this->assertContains('"title":"Title String"', $response->getContent());
		
		// Test that response is not empty
		$this->assertNotEmpty($response->getContent());
	}
	
	protected function createEvent()
	{
		$event = new Event;
		
		$event
			->setTitle('Title String')
			->setDate(new \DateTime('2017-04-10 08:00:00'))
			->setImpact(5)
			->setInstrument('Instrument String')
			->setActual(5.1)
			->setForecast(6.2);
		
		$em = $this->getDoctrine()->getManager();
		$em->persist($event);
		$em->flush();
		
		return $event->getId();
	}
}
