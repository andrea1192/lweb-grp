<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	class Movies extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'movies';
		protected static $document;
		protected static $xpath;

		public static function getMovies() {
			$query = "/movies/*";

			$movies = self::queryDocument($query);

			return new \models\MovieList($movies);
		}

		public static function getMovieById($id) {
			$movie = self::getElementById($id);

			return new \models\Movie($movie);
		}
	}

	class Requests extends Movies {
		protected const DOCUMENT_NAME = 'requests';
		protected static $document;
		protected static $xpath;

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

			return new \models\Request($request);
		}
	}

	class MovieList extends \IteratorIterator {

		public function current(): \models\Movie {

			return new \models\Movie(parent::current());
		}
	}

	class RequestList extends \IteratorIterator {

		public function current(): \models\Request {

			return new \models\Request(parent::current());
		}
	}
?>