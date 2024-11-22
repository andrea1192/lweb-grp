<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	abstract class AbstractMovies extends \models\XMLDocument {
		public const POSTERS_PATH = DIR_POSTERS;
		public const BACKDROPS_PATH = DIR_BACKDROPS;
		public const MEDIA_TYPE = MEDIA_TYPE;
		public const MEDIA_EXT = MEDIA_EXT;

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
	}

	class Movies extends AbstractMovies {
		protected const DOCUMENT_NAME = 'movies';
		protected const ELEMENT_NAME = 'movie';

		public function getMovies() {
			$query = "/movies/*";
			$matches = $this->xpath->query($query);

			return new \models\MovieList($matches);
		}

		public function getMovieById($id) {
			return $this->read($id);
		}
	}

	class Requests extends AbstractMovies {
		protected const DOCUMENT_NAME = 'requests';
		protected const ELEMENT_NAME = 'request';

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
			return $this->read($id);
		}
	}

	class MovieList extends \IteratorIterator {

		public function current(): \models\Movie {
			$state = MovieMapper::createStateFromElement(parent::current());
			return \models\Movie::build('movie', $state);
		}
	}

	class RequestList extends \IteratorIterator {

		public function current(): \models\Request {
			$state = RequestMapper::createStateFromElement(parent::current());
			return \models\Request::build('request', $state);
		}
	}
?>