<?php

date_default_timezone_set('Europe/Berlin');

require_once 'vendor/autoload.php';
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

$xml = file_get_contents('https://14.openmind-konferenz.de/events.xml');

$prog = new SimpleXMLElement($xml);

foreach ($prog->day as $day) {
	$date = (string)$day['date'];
	foreach ($day->room as $room) {
		$events = array();
		$room_name = (string)$room['name'];
		foreach ($room->event as $event) {
			$data = array();
			$data['id'] = (string)$event['id'];
			$data['title'] = (string)$event->title;
			$data['subtitle'] = (string)$event->subtitle;
			$data['abstract'] = (string)$event->abstract;
			$data['description'] = (string)$event->description;
			$data['start'] = new DateTime($date . ' ' . $event->start);
			$splitduration = explode(':', (string)$event->duration);
			$data['duration'] = new DateInterval('PT' . $splitduration[0] . 'H' . $splitduration[1] . 'M');
			$data['end'] = clone $data['start'];
			$data['end']->add($data['duration']);
			$data['persons'] = array();
			foreach ($event->persons->person as $person) {
				$data['persons'][] = (string)$person;
			}
			$data['persons'] = implode(', ', $data['persons']);
			$events[] = $data;
		}
		file_put_contents($date . ' ' . $room_name . '.html', $twig->render('day-room.html', array(
			'date' => $date,
			'room' => $room_name,
			'events' => $events,
		)));
	}
}
