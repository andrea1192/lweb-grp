<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Reaction.php');

	class Reactions extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'reactions';

		public static function getMapperForItem($subject) {
			$class = get_class($subject);

			if ($class == 'DOMElement')
				$name = $subject->nodeName;
			else
				$name = str_replace('models\\', '', strtolower($class));

			switch ($name) {
				case 'like':
					return '\models\Likes';
				case 'usefulness':
					return '\models\Usefulnesses';
				case 'agreement':
					return '\models\Agreements';
				case 'spoilage':
					return '\models\Spoilages';
				case 'answer':
					return '\models\Answers';
				case 'report':
					return '\models\Reports';
			}
		}

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				return;

			$object->post = $element->getAttribute('post');
			$object->author = $element->getAttribute('author');

			return $object;
		}

		public function getReactionsByPost($post_id, $type = '*') {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getReactionsByAuthor($author, $type = '*') {
			$query = "/reactions/{$type}[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getReactionCountByPost($post_id, $type, $type_bin) {
			$query = "/reactions/{$type}[@post='{$post_id}' and @type='{$type_bin}']";
			$matches = $this->xpath->query($query);

			return count($matches);
		}

		public function getReactionAverageByPost($post_id, $type) {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = $this->xpath->query($query);

			$sum = 0;

			foreach ($matches as $match) {
				$mapper = Reactions::getMapperForItem($match);
				$reaction = $mapper::createObjectFromElement($match);
				$sum += $reaction->rating;
			}

			return (count($matches)) ? ($sum / count($matches)) : 0;
		}

		protected function getReaction($post_id, $author, $type) {
			$root = static::DOCUMENT_NAME;

			$query = "/{$root}/{$type}[@post='{$post_id}' and @author='{$author}']";
			$match = $this->xpath->query($query)->item(0);

			return $match;
		}

		public function save($object) {
			$mapper = static::getMapperForItem($object);
			$element = $mapper::createElementFromObject($object, $this->document);

			if ($this->getReaction($object->post, $object->author, $mapper::ELEMENT_NAME))
				$this->replaceReaction($object->post, $object->author, $element);
			else
				$this->appendElement($element);

			return $object;
		}

		protected function replaceReaction($post_id, $author, $node) {
			$mapper = static::getMapperForItem($node);

			$root = $mapper::DOCUMENT_NAME;
			$elem = $mapper::ELEMENT_NAME;

			$query = "/{$root}/{$elem}[@post='{$post_id}' and @author='{$author}']";
			$this->xpath->query($query)->item(0)->replaceWith($node);

			$this->saveDocument();
		}
	}

	class BinaryRatings extends Reactions {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = \models\Reaction::createReaction($element->nodeName);

			$object = parent::createObjectFromElement($element, $object);
			$object->type = $element->getAttribute('type');

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement(static::ELEMENT_NAME);

			$attributes = [
				'post' => '',
				'author' => '',
				'type' => ''
			];

			foreach ($attributes as $key => $value) {
				$attributes[$key] = $document->createAttribute($key);
				$attributes[$key]->value = $object->$key;
				$element->appendChild($attributes[$key]);
			}

			return $element;
		}
	}

	class NumericRatings extends Reactions {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = \models\Reaction::createReaction($element->nodeName);

			$object = parent::createObjectFromElement($element, $object);
			$object->rating = $element->getAttribute('rating');

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement(static::ELEMENT_NAME);

			$attributes = [
				'post' => '',
				'author' => '',
				'rating' => ''
			];

			foreach ($attributes as $key => $value) {
				$attributes[$key] = $document->createAttribute($key);
				$attributes[$key]->value = $object->$key;
				$element->appendChild($attributes[$key]);
			}

			return $element;
		}
	}

	class Likes extends BinaryRatings {
		protected const ELEMENT_NAME = 'like';
	}

	class Usefulnesses extends NumericRatings {
		protected const ELEMENT_NAME = 'usefulness';
	}

	class Agreements extends NumericRatings {
		protected const ELEMENT_NAME = 'agreement';
	}

	class Spoilages extends NumericRatings {
		protected const ELEMENT_NAME = 'spoilage';
	}

	class Answers extends Reactions {
		protected const DOCUMENT_NAME = 'answers';
		protected const ELEMENT_NAME = 'answer';
		protected const ID_PREFIX = 'a';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Answer();

			$object = parent::createObjectFromElement($element, $object);

			$object->id = $element->getAttribute('id');
			$object->status = $element->getAttribute('status');
			$object->date = $element->getAttribute('date');
			$object->text = $element->getElementsByTagName('text')->item(0)->textContent;

			$object->reactions = [
					'usefulness' => new \models\NumericReactionType($object->id, 'usefulness'),
					'agreement' => new \models\NumericReactionType($object->id, 'agreement')
				];

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('answer');

			$attributes = [
				'id' => '',
				'status' => '',
				'post' => '',
				'author' => '',
				'date' => ''
			];

			foreach ($attributes as $key => $value) {
				$attributes[$key] = $document->createAttribute($key);
				$attributes[$key]->value = $object->$key;
				$element->appendChild($attributes[$key]);
			}

			$text = $document->createElement('text');
			$text->textContent = $object->text;
			$element->appendChild($text);

			return $element;
		}

		public function getFeaturedAnswer($post_id) {
			$answer_id = $this->getMapper('posts')->getPostById($post_id)->featuredAnswer;

			if ($answer_id)
				return $this->getAnswerById($answer_id);
		}

		public function getAnswersByPost($post_id) {
			$query = "/answers/answer[@post='{$post_id}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getAnswersByAuthor($author) {
			$query = "/answers/answer[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getAnswerById($id) {
			$answer = $this->document->getElementById($id);

			$mapper = Answers::getMapperForItem($answer);
			return $mapper::createObjectFromElement($answer);
		}

		public function save($object) {

			if (empty($object->id)) {
				$root = self::DOCUMENT_NAME;
				$elem = self::ELEMENT_NAME;
				$prefix = self::ID_PREFIX;

				$object->id = $this->generateID($root, $elem, $prefix);
			}

			$element = self::createElementFromObject($object, $this->document);

			if ($this->document->getElementById($object->id))
				$this->replaceElement($object->id, $element);
			else
				$this->appendElement($element);

			return $object;
		}
	}

	class Reports extends Reactions {
		protected const DOCUMENT_NAME = 'reports';
		protected const ELEMENT_NAME = 'report';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Report();

			$object = parent::createObjectFromElement($element, $object);
			$unavail = new class {public $textContent = 'N/A';};

			$object->date = $element->getAttribute('date');
			$object->status = $element->getAttribute('status');
			$object->message =
				($element->getElementsByTagName('message')->item(0) ?? $unavail)
				->textContent;
			$object->response =
				($element->getElementsByTagName('response')->item(0) ?? $unavail)
				->textContent;

			return $object;
		}

		public static function createElementFromObject($object, $document, $element = null) {
			if (!$element)
				$element = $document->createElement('report');

			$attributes = [
				'post' => '',
				'author' => '',
				'date' => '',
				'status' => ''
			];

			foreach ($attributes as $key => $value) {
				$attributes[$key] = $document->createAttribute($key);
				$attributes[$key]->value = $object->$key;
				$element->appendChild($attributes[$key]);
			}

			$message = $document->createElement('message');
			$response = $document->createElement('response');
			$message->textContent = $object->message;
			$response->textContent = $object->response;
			$element->appendChild($message);
			$element->appendChild($response);

			return $element;
		}

		public function getReports() {
			$query = "/reports/*";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getReportsByAuthor($author) {
			$query = "/reports/report[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}
	}

	class ReactionList extends \IteratorIterator {

		public function current(): \models\Reaction {
			$element = parent::current();
			$mapper = \models\Reactions::getMapperForItem($element);
			return $mapper::createObjectFromElement($element);
		}
	}
?>