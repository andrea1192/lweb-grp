<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	abstract class AbstractMovies extends \models\XMLDocument {
		public const POSTERS_PATH = 'static/posters/';
		public const BACKDROPS_PATH = 'static/backdrops/';
		public const MEDIA_TYPE = 'image/jpeg';
		public const MEDIA_EXT = '.jpg';

		public static function getMapperForItem($subject) {
			$class = get_class($subject);

			if ($class == 'DOMElement')
				$name = $subject->nodeName;
			else
				$name = str_replace('models\\', '', strtolower($class));

			switch ($name) {
				case 'movie':
					return '\models\Movies';
				case 'request':
					return '\models\Requests';
			}
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
		protected const ELEMENT_NAME = 'movie';

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

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('movie');

			$element = parent::createElementFromObject($object, $document, $element);
			$keys = [
				'duration' => '',
				'summary' => '',
				'director' => '',
				'writer' => ''
			];

			foreach ($keys as $key => $value) {
				$keys[$key] = $document->createElement($key);
				$keys[$key]->textContent = $object->$key;
				$element->appendChild($keys[$key]);
			}

			return $element;
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
		protected const ELEMENT_NAME = 'request';

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
			$object->author = $element->getAttribute('author');
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

				if (!empty($object->$key) && $object->$key != 'N/A') {

					$keys[$key]->textContent = $object->$key;
					$element->appendChild($keys[$key]);
				}
			}

			$status = $document->createAttribute('status');
			$author = $document->createAttribute('author');
			$status->value = $object->status;
			$author->value = $object->author;
			$element->appendChild($status);
			$element->appendChild($author);

			return $element;
		}

		public function getRequests() {
			$query = "/requests/*[@status!='deleted']";
			$matches = $this->xpath->query($query);

			return new \models\RequestList($matches);
		}

		private function getRequestsByStatus($status) {
			$query = "/requests/*[@status='{$status}']";
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