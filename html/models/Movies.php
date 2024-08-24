<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	abstract class AbstractMovies extends \models\XMLDocument {
		protected const DOCUMENT_NAME = '';
		protected static $document;
		protected static $xpath;

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				return;

			$object->id = $element->getAttribute('id');
			$object->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$object->year = $element->getElementsByTagName('year')->item(0)->textContent;

			return $object;
		}
	}

	class Movies extends AbstractMovies {
		protected const DOCUMENT_NAME = 'movies';
		protected static $document;
		protected static $xpath;

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Movie();

			$object = parent::createObjectFromElement($element, $object);
			$object->duration = $element->getElementsByTagName('duration')->item(0)->textContent;
			$object->summary = $element->getElementsByTagName('summary')->item(0)->textContent;
			$object->director = $element->getElementsByTagName('director')->item(0)->textContent;
			$object->writer = $element->getElementsByTagName('writer')->item(0)->textContent;

			$object->posts = \models\Posts::getPostsByMovie($object->id);

			return $object;
		}

		public static function getMovies() {
			$query = "/movies/*";

			$movies = self::queryDocument($query);

			return new \models\MovieList($movies);
		}

		public static function getMovieById($id) {
			$movie = self::getElementById($id);

			return static::createObjectFromElement($movie);
		}
	}

	class Requests extends AbstractMovies {
		protected const DOCUMENT_NAME = 'requests';
		protected static $document;
		protected static $xpath;

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
			$object->posts = \models\Comments::getCommentsByRequest($object->id);

			return $object;
		}

		public static function getRequests() {
			$query = "/requests/*";

			$requests = self::queryDocument($query);

			return new \models\RequestList($requests);
		}

		private static function getRequestsByStatus($status) {
			$query = "/requests/*[status='{$status}']";

			$requests = self::queryDocument($query);

			return new \models\RequestList($requests);
		}

		public static function getSubmittedRequests() {
			return self::getRequestsByStatus('submitted');
		}

		public static function getAcceptedRequests() {
			return self::getRequestsByStatus('accepted');
		}

		public static function getRejectedRequests() {
			return self::getRequestsByStatus('rejected');
		}

		public static function getRequestById($id) {
			$request = self::getElementById($id);

			return static::createObjectFromElement($request);
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