<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 */
class Event
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $title;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $date;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $impact;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $instrument;
	
	/**
	 * @ORM\Column(type="decimal", scale=2)
	 */
	protected $actual;
	
	/**
	 * @ORM\Column(type="decimal", scale=2)
	 */
	protected $forecast;
	
	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * @param string $title
	 * @return Event
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}
	
	/**
	 * @param \DateTime $date
	 * @return Event
	 */
	public function setDate($date)
	{
		$this->date = $date;
	
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getImpact()
	{
		return $this->impact;
	}
	
	/**
	 * @param integer $impact
	 * @return Event
	 */
	public function setImpact($impact)
	{
		$this->impact = $impact;
	
		return $this;
	}

	/**
	 * @return string
	 */
	public function getInstrument()
	{
		return $this->instrument;
	}
	
	/**
	 * @param string $instrument
	 * @return Event
	 */
	public function setInstrument($instrument)
	{
		$this->instrument = $instrument;
	
		return $this;
	}

	/**
	 * @return float
	 */
	public function getActual()
	{
		return $this->actual;
	}
	
	/**
	 * @param float $actual
	 * @return Event
	 */
	public function setActual($actual)
	{
		$this->actual = $actual;
	
		return $this;
	}

	/**
	 * @return float
	 */
	public function getForecast()
	{
		return $this->forecast;
	}
	
	/**
	 * @param float $forecast
	 * @return Event
	 */
	public function setForecast($forecast)
	{
		$this->forecast = $forecast;
	
		return $this;
	}
}

