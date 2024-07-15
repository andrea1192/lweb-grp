<?php namespace models;

	require_once('models/Post.php');

	class Posts {
		private static $document;

		private static function loadDocument() {
			self::$document = new \DOMDocument('1.0', 'UTF-8');

			self::$document->load('static/posts.xml');
			self::$document->schemaValidate('schemas/posts.xsd');
		}

		public static function getPosts($movie_id) {
	
			if (!(self::$document))
				self::loadDocument();

			$results = self::$document->getElementsByTagName('review');

			return new \models\PostList($results);
		}
	}

	class PostList extends \IteratorIterator {

		public function current(): \models\Post {

			return new \models\Post(parent::current());
		}
	}
?>