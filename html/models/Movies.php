<?php namespace models;

	require_once('models/Movie.php');

	class Movies {
		private static $document;

		private static function loadDocument() {
			self::$document = new \DOMDocument('1.0', 'UTF-8');

			self::$document->load('static/movies.xml');
			self::$document->schemaValidate('schemas/movies.xsd');
		}

		public static function getMovie($id) {
	
			if (!(self::$document))
				self::loadDocument();

			$movie = self::$document->getElementById($id);

			return new \models\Movie($movie);
		}
	}
?>