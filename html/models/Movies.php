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