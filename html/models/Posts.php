<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Post.php');

	class Posts extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'posts';
		protected static $document;
		protected static $xpath;

		public static function classify($element) {

			switch ($element->nodeName) {
				default:
					return 'Posts';
				case 'comment':
					return 'Comments';
				case 'review':
					return 'Reviews';
				case 'question':
					return 'Questions';
				case 'spoiler':
					return 'Spoilers';
				case 'extra':
					return 'Extras';
			}
		}

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				return;

			$object->id = $element->getAttribute('id');
			$object->movie = $element->getAttribute('movie');
			$object->author = $element->getAttribute('author');
			$object->date = $element->getAttribute('date');

			$object->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$object->text = $element->getElementsByTagName('text')->item(0)->textContent;

			return $object;
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

		public static function getPostById($id) {
			$post = self::getElementById($id);

			$class = '\\models\\'.Posts::classify($post);
			return $class::createObjectFromElement($post);
		}
	}

	class Comments extends Posts {
		protected const DOCUMENT_NAME = 'comments';
		protected static $document;
		protected static $xpath;

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Comment();

			$object = parent::createObjectFromElement($element, $object);
			$object->request = $element->getAttribute('request');
			$object->rating = $element->getElementsByTagName('rating')->item(0)->textContent;

			return $object;
		}

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
			$comment = self::getElementById($id);

			$class = '\\models\\'.Posts::classify($post);
			return $class::createObjectFromElement($post);
		}
	}

	class Reviews extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Review();

			$object = parent::createObjectFromElement($element, $object);
			$object->rating = $element->getElementsByTagName('rating')->item(0)->textContent;

			$object->reactions = [
				'like' => new \models\BinaryReactionType($object->id, 'like', 'like', 'dislike')
			];

			return $object;
		}
	}

	class Questions extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Question();

			$object = parent::createObjectFromElement($element, $object);
			$object->featured = (boolean) $element->getAttribute('featured');
			$object->featuredAnswer = (string) $element->getAttribute('featuredAnswer');

			$object->answers = \models\Answers::getAnswersByPost($object->id);
			$object->reactions = [
				'usefulness' => new \models\NumericReactionType($object->id, 'usefulness'),
				'agreement' => new \models\NumericReactionType($object->id, 'agreement')
			];

			return $object;
		}
	}

	class Spoilers extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Spoiler();

			$object = parent::createObjectFromElement($element, $object);
			$object->rating = $element->getElementsByTagName('rating')->item(0)->textContent;

			$object->reactions = [
				'spoilage' => new \models\NumericReactionType($object->id, 'spoilage')
			];

			return $object;
		}
	}

	class Extras extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Extra();

			$object = parent::createObjectFromElement($element, $object);
			$object->reputation = $element->getAttribute('reputation');

			return $object;
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
			$element = parent::current();
			$class = '\\models\\'.Posts::classify($element);
			return $class::createObjectFromElement($element);
		}
	}
?>