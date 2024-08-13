<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Post.php');

	class Posts extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'posts';
		protected static $document;
		protected static $xpath;

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

		public static function getPostById($id) {

			if (!(self::$document))
				self::loadDocument();

			$post = self::$document->getElementById($id);

			return \models\Post::generatePost($post);
		}
	}

	class Comments extends Posts {
		protected const DOCUMENT_NAME = 'comments';
		protected static $document;
		protected static $xpath;

		public static function getCommentsByRequest($movie_id) {
			$query = "/comments/comment[@request='{$movie_id}']";

			$matches = self::queryDocument($query);

			return new \models\PostList($matches);
		}

		public static function getCommentsByAuthor($author) {
			$query = "/comments/comment[@author='{$author}']";

			$matches = self::queryDocument($query);

			return new \models\PostList($matches);
		}

		public static function getCommentById($id) {

			if (!(self::$document))
				self::loadDocument();

			$comment = self::$document->getElementById($id);

			return \models\Post::generatePost($comment);
		}
	}

	class PostList extends \IteratorIterator implements \Countable {
		private $count;

		public function __construct($iterator) {
			parent::__construct($iterator);

			$this->count = $iterator->count();
		}

		public function count(): int {

			return $this->count;
		}

		public function current(): \models\Post {

			return \models\Post::generatePost(parent::current());
		}
	}
?>