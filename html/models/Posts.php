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
	}

	class PostList extends \IteratorIterator {

		public function current(): \models\Post {

			return \models\Post::generatePost(parent::current());
		}
	}
?>