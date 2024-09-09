<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Post.php');

	class Posts extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'posts';

		public static function getMapperForItem($subject) {
			if (!$subject)
				return '\models\DeletedPosts';

			$class = get_class($subject);

			if ($class == 'DOMElement')
				$name = $subject->nodeName;
			else
				$name = str_replace('models\\', '', strtolower($class));

			switch ($name) {
				case 'comment':
					return '\models\Comments';
				case 'review':
					return '\models\Reviews';
				case 'question':
					return '\models\Questions';
				case 'spoiler':
					return '\models\Spoilers';
				case 'extra':
					return '\models\Extras';
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
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getPostsByAuthor($author, $type = '*') {
			$query = "/posts/{$type}[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getPostById($id) {
			$post = $this->document->getElementById($id);

			$mapper = Posts::getMapperForItem($post);
			return $mapper::createObjectFromElement($post);
		}
	}

	class Comments extends Posts {
		protected const DOCUMENT_NAME = 'comments';
		protected const ELEMENT_NAME = 'comment';
		protected const ID_PREFIX = 'c';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Comment();

			$object = parent::createObjectFromElement($element, $object);
			$object->request = $element->getAttribute('request');
			$object->rating = $element->getElementsByTagName('rating')->item(0)->textContent;

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('comment');

			$id = $document->createAttribute('id');
			$request = $document->createAttribute('request');
			$author = $document->createAttribute('author');
			$date = $document->createAttribute('date');

			$id->value = $object->id;
			$request->value = $object->request;
			$author->value = $object->author;
			$date->value = $object->date;

			$element->appendChild($id);
			$element->appendChild($request);
			$element->appendChild($author);
			$element->appendChild($date);

			$rating = $document->createElement('rating');
			$rating->textContent = $object->rating;
			$element->appendChild($rating);

			$element = parent::mapCommonElements($object, $document, $element);

			return $element;
		}

		public function getCommentsByRequest($movie_id) {
			$query = "/comments/comment[@request='{$movie_id}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getCommentsByAuthor($author) {
			$query = "/comments/comment[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getCommentById($id) {
			$comment = $this->document->getElementById($id);

			$mapper = Posts::getMapperForItem($comment);
			return $mapper::createObjectFromElement($comment);
		}
	}

	class Reviews extends Posts {
		protected const ELEMENT_NAME = 'review';
		protected const ID_PREFIX = 'r';

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
		protected const ELEMENT_NAME = 'question';
		protected const ID_PREFIX = 'q';

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
		protected const ELEMENT_NAME = 'spoiler';
		protected const ID_PREFIX = 's';

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
		protected const ELEMENT_NAME = 'extra';
		protected const ID_PREFIX = 'e';

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

	class DeletedPosts extends Posts {

		public static function createObjectFromElement($element, $object = null) {
			return new DeletedPost();
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
			$mapper = \models\Posts::getMapperForItem($element);
			return $mapper::createObjectFromElement($element);
		}
	}
?>