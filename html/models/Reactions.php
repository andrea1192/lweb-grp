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
	}

	class Likes extends Reactions {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Like();

			$object = parent::createObjectFromElement($element, $object);
			$object->type = $element->getAttribute('type');

			return $object;
		}
	}

	class Usefulnesses extends Reactions {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Usefulness();

			$object = parent::createObjectFromElement($element, $object);
			$object->rating = $element->getAttribute('rating');

			return $object;
		}
	}

	class Agreements extends Reactions {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Agreement();

			$object = parent::createObjectFromElement($element, $object);
			$object->rating = $element->getAttribute('rating');

			return $object;
		}
	}

	class Spoilages extends Reactions {

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Spoilage();

			$object = parent::createObjectFromElement($element, $object);
			$object->rating = $element->getAttribute('rating');

			return $object;
		}
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
	}

	class Reports extends Reactions {
		protected const DOCUMENT_NAME = 'reports';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Report();

			$object = parent::createObjectFromElement($element, $object);
			$unavail = new class {public $textContent = 'N/A';};

			$object->status = $element->getAttribute('status');
			$object->message =
				($element->getElementsByTagName('message')->item(0) ?? $unavail)
				->textContent;
			$object->response =
				($element->getElementsByTagName('response')->item(0) ?? $unavail)
				->textContent;

			return $object;
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

		public function getReportByPostIdAuthor($post_id, $author) {
			$query = "/reports/report[@post='{$post_id}' and @author='{$author}']";
			$match = $this->xpath->query($query)->item(0);

			return $this->createObjectFromElement($match);
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