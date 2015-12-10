<?php

class TrafficMessage
{
	private $id;
	private $priority;
	private $createddate;
	private $title;
	private $exactlocation;
	private $description;
	private $latitude;
	private $longitude;
	private $category;
	private $subcategory;

	public function __construct($incident)
	{
		$this->id = $incident["id"];
		$this->priority = $incident["priority"];
		$this->createddate = $incident["createddate"];
		$this->title = $incident["title"];
		$this->exactlocation = $incident["exactlocation"];
		$this->description = $incident["description"];
		$this->latitude = $incident["latitude"];
		$this->longitude = $incident["longitude"];
		$this->category = $incident["category"];
		$this->subcategory = $incident["subcategory"];
	}

	public function getId()
	{
		return $this->id;
	}

	public function getPriority()
	{
		return $this->priority;
	}

	public function getCreateddate()
	{
		$date = substr($this->createddate, 6, 10);
		$date = new DateTime("@$date");
		return $date->format('j M Y');
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getExactlocation()
	{
		return $this->exactlocation;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getLatitude()
	{
		return $this->latitude;
	}

	public function getLongitude()
	{
		return $this->longitude;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function getSubcategory()
	{
		return $this->subcategory;
	}
}