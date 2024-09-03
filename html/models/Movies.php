<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	abstract class AbstractMovies extends \models\XMLDocument {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				return;

			$object->id = $element->getAttribute('id');
			$object->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$object->year = $element->getElementsByTagName('year')->item(0)->textContent;

			return $object;
		}

		public function createElementFromObject($object, $element = null) {
			if (!$element)
				return;

			$id = $this->document->createAttribute('id');
			$title = $this->document->createElement('title');
			$year = $this->document->createElement('year');

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

			$movies = $this->queryDocument($query);

			return new \models\MovieList($movies);
		}

		public function getMovieById($id) {
			$movie = $this->getElementById($id);

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

		public function createElementFromObject($object, $element = null) {
			if (!$element)
				$element = $this->document->createElement('request');

			$element = parent::createElementFromObject($object, $element);
			$keys = [
				'duration' => '',
				'summary' => '',
				'director' => '',
				'writer' => ''
			];

			foreach ($keys as $key => $value) {
				$keys[$key] = $this->document->createElement($key);

				if ($object->$key != 'N/A') {
					$keys[$key]->textContent = $object->$key;
					$element->appendChild($keys[$key]);
				}
			}

			$status = $this->document->createAttribute('status');
			$status->value = $object->status;
			$element->appendChild($status);

			return $element;
		}

		public function getRequests() {
			$query = "/requests/*";

			$requests = $this->queryDocument($query);

			return new \models\RequestList($requests);
		}

		private function getRequestsByStatus($status) {
			$query = "/requests/*[status='{$status}']";

			$requests = $this->queryDocument($query);

			return new \models\RequestList($requests);
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
			$request = $this->getElementById($id);

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