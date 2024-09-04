<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Post.php');

	class Posts extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'posts';

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

		public static function classifyObject($object) {

			switch (get_class($object)) {
				default:
					return 'Posts';
				case 'models\Comment':
					return 'Comments';
				case 'models\Review':
					return 'Reviews';
				case 'models\Question':
					return 'Questions';
				case 'models\Spoiler':
					return 'Spoilers';
				case 'models\Extra':
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

		public static function mapCommonAttributes($object, $document, $element) {
			$id = $document->createAttribute('id');
			$movie = $document->createAttribute('movie');
			$author = $document->createAttribute('author');
			$date = $document->createAttribute('date');

			$id->value = $object->id;
			$movie->value = $object->movie;
			$author->value = $object->author;
			$date->value = $object->date;

			$element->appendChild($id);
			$element->appendChild($movie);
			$element->appendChild($author);
			$element->appendChild($date);

			return $element;
		}

		public static function mapCommonElements($object, $document, $element) {
			$title = $document->createElement('title');
			$text = $document->createElement('text');

			$title->textContent = $object->title;
			$text->textContent = $object->text;

			$element->appendChild($title);
			$element->appendChild($text);

			return $element;
		}

		public function getPostsByMovie($movie_id, $type = '*') {
			$query = "/posts/{$type}[@movie='{$movie_id}']";

			$matches = $this->queryDocument($query);

			return new \models\PostList($matches);
		}

		public function getPostsByAuthor($author, $type = '*') {
			$query = "/posts/{$type}[@author='{$author}']";

			$matches = $this->queryDocument($query);

			return new \models\PostList($matches);
		}

		public function getPostById($id) {
			$post = $this->getElementById($id);

			$class = '\\models\\'.Posts::classify($post);
			return $class::createObjectFromElement($post);
		}
	}

	class Comments extends Posts {
		protected const DOCUMENT_NAME = 'comments';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Comment();

			$object = parent::createObjectFromElement($element, $object);
			$object->request = $element->getAttribute('request');
			$object->rating = $element->getElementsByTagName('rating')->item(0)->textContent;

			return $object;
		}

		public function getCommentsByRequest($movie_id) {
			$query = "/comments/comment[@request='{$movie_id}']";

			$matches = $this->queryDocument($query);

			return new \models\PostList($matches);
		}

		public function getCommentsByAuthor($author) {
			$query = "/comments/comment[@author='{$author}']";

			$matches = $this->queryDocument($query);

			return new \models\PostList($matches);
		}

		public function getCommentById($id) {
			$comment = $this->getElementById($id);

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

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('review');

			$element = parent::mapCommonAttributes($object, $document, $element);

			$rating = $document->createElement('rating');
			$rating->textContent = $object->rating;
			$element->appendChild($rating);

			$element = parent::mapCommonElements($object, $document, $element);

			return $element;
		}
	}

	class Questions extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Question();

			$object = parent::createObjectFromElement($element, $object);
			$object->featured = (boolean) $element->getAttribute('featured');
			$object->featuredAnswer = (string) $element->getAttribute('featuredAnswer');

			$object->answers = self::getMapper('answers')->getAnswersByPost($object->id);
			$object->reactions = [
				'usefulness' => new \models\NumericReactionType($object->id, 'usefulness'),
				'agreement' => new \models\NumericReactionType($object->id, 'agreement')
			];

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('question');

			$element = parent::mapCommonAttributes($object, $document, $element);

			$featured = $document->createAttribute('featured');
			$featuredAnswer = $document->createAttribute('featuredAnswer');

			$featured->value = $object->featured;
			$featuredAnswer->value = $object->featuredAnswer;

			$element->appendChild($featured);
			$element->appendChild($featuredAnswer);

			$element = parent::mapCommonElements($object, $document, $element);

			return $element;
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

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('spoiler');

			$element = parent::mapCommonAttributes($object, $document, $element);

			$rating = $document->createElement('rating');
			$rating->textContent = $object->rating;
			$element->appendChild($rating);

			$element = parent::mapCommonElements($object, $document, $element);

			return $element;
		}
	}

	class Extras extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Extra();

			$object = parent::createObjectFromElement($element, $object);
			$object->reputation = $element->getAttribute('rep');

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('extra');

			$element = parent::mapCommonAttributes($object, $document, $element);

			$reputation = $document->createAttribute('rep');
			$reputation->textContent = $object->reputation;
			$element->appendChild($reputation);

			$element = parent::mapCommonElements($object, $document, $element);

			return $element;
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