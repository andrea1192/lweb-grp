<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	abstract class AbstractMovies extends \models\XMLDocument {

		public static function getMapperForItem($object) {
			return '\models\Requests';
		}

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				return;

			$object->id = $element->getAttribute('id');
			$object->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$object->year = $element->getElementsByTagName('year')->item(0)->textContent;

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				return;

			$id = $document->createAttribute('id');
			$title = $document->createElement('title');
			$year = $document->createElement('year');

			$id->value = $object->id;
			$title->textContent = $object->title;
			$year->textContent = $object->year;

			$element->appendChild($id);
			$element->appendChild($title);
			$element->appendChild($year);

			return $element;
		}
	}

	class Movies extends AbstractMovies {
		protected const DOCUMENT_NAME = 'movies';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Movie();

			$object = parent::createObjectFromElement($element, $object);
			$object->duration = $element->getElementsByTagName('duration')->item(0)->textContent;
			$object->summary = $element->getElementsByTagName('summary')->item(0)->textContent;
			$object->director = $element->getElementsByTagName('director')->item(0)->textContent;
			$object->writer = $element->getElementsByTagName('writer')->item(0)->textContent;

			$object->posts = self::getMapper('posts')->getPostsByMovie($object->id);

			return $object;
		}

		public function getMovies() {
			$query = "/movies/*";
			$matches = $this->xpath->query($query);

			return new \models\MovieList($matches);
		}

		public function getMovieById($id) {
			$movie = $this->document->getElementById($id);

			return $this->createObjectFromElement($movie);
		}
	}

	class Requests extends AbstractMovies {
		protected const DOCUMENT_NAME = 'requests';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Request();

			$object = parent::createObjectFromElement($element, $object);
			$unavail = new class {public $textContent = 'N/A';};
			$object->duration =
				($element->getElementsByTagName('duration')->item(0) ?? $unavail)
				->textContent;
			$object->summary =
				($element->getElementsByTagName('summary')->item(0) ?? $unavail)
				->textContent;
			$object->director =
				($element->getElementsByTagName('director')->item(0) ?? $unavail)
				->textContent;
			$object->writer =
				($element->getElementsByTagName('writer')->item(0) ?? $unavail)
				->textContent;

			$object->status = $element->getAttribute('status');
			$object->posts = self::getMapper('comments')->getCommentsByRequest($object->id);

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('request');

			$element = parent::createElementFromObject($object, $document, $element);
			$keys = [
				'duration' => '',
				'summary' => '',
				'director' => '',
				'writer' => ''
			];

			foreach ($keys as $key => $value) {
				$keys[$key] = $document->createElement($key);

				if ($object->$key != 'N/A') {
					$keys[$key]->textContent = $object->$key;
					$element->appendChild($keys[$key]);
				}
			}

			$status = $document->createAttribute('status');
			$status->value = $object->status;
			$element->appendChild($status);

			return $element;
		}

		public function getRequests() {
			$query = "/requests/*";
			$matches = $this->xpath->query($query);

			return new \models\RequestList($matches);
		}

		private function getRequestsByStatus($status) {
			$query = "/requests/*[status='{$status}']";
			$matches = $this->xpath->query($query);

			return new \models\RequestList($matches);
		}

		public function getSubmittedRequests() {
			return $this->getRequestsByStatus('submitted');
		}

		public function getAcceptedRequests() {
			return $this->getRequestsByStatus('accepted');
		}

		public function getRejectedRequests() {
			return $this->getRequestsByStatus('rejected');
		}

		public function getRequestById($id) {
			$request = $this->document->getElementById($id);

			return $this->createObjectFromElement($request);
		}
	}

	class MovieList extends \IteratorIterator {

		public function current(): \models\Movie {

			return Movies::createObjectFromElement(parent::current());
		}
	}

	class RequestList extends \IteratorIterator {

		public function current(): \models\Request {

			return Requests::createObjectFromElement(parent::current());
		}
	}
?>