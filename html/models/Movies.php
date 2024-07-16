<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Movie.php');

	class Movies extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'movies';
		protected static $document;
		protected static $xpath;

		public static function getMovie($id) {

			if (!(self::$document))
				self::loadDocument();

			$movie = self::$document->getElementById($id);

			return new \models\Movie($movie);
		}
	}
?>