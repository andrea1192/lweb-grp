<?php namespace models;

	require_once('models/Post.php');

	class Posts {
		private static $document;
		private static $xpath;

		private static function loadDocument() {
			self::$document = new \DOMDocument('1.0', 'UTF-8');

			self::$document->load('static/posts.xml');
			self::$document->schemaValidate('schemas/posts.xsd');
		}

		private static function queryDocument($query) {

			if (!(self::$document)) {
				self::loadDocument();
				self::$xpath = new \DOMXPath(self::$document);
			}

			return self::$xpath->query($query, self::$document);
		}

		public static function getPostsByMovie($movie_id, $type = '*') {
			$query = "/posts/{$type}[@movie='{$movie_id}']";

			$matches = self::queryDocument($query);

			return new \models\PostList($matches);
		}

		public static function getPostsByAuthor($author, $type = '*') {
			$query = "/posts/{$type}[@author='{$author}']";

			$matches = self::queryDocument($query);

			return new \models\PostList($matches);
		}
	}

	class PostList extends \IteratorIterator {

		public function current(): \models\Post {

			return \models\Post::generatePost(parent::current());
		}
	}
?>