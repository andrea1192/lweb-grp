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

			if (!(self::$document))
				self::loadDocument();

			$movie = self::$document->getElementById($id);

			return new \models\Movie($movie);
		}
	}

	class MovieList extends \IteratorIterator {

		public function current(): \models\Movie {

			return new \models\Movie(parent::current());
		}
	}
?>