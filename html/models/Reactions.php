<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Reaction.php');

	class Reactions extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'reactions';

		public static function classify($element) {

			switch ($element->nodeName) {
				default:
					return 'Reactions';
				case 'like':
					return 'Likes';
				case 'usefulness':
					return 'Usefulnesses';
				case 'agreement':
					return 'Agreements';
				case 'spoilage':
					return 'Spoilages';
				case 'answer':
					return 'Answers';
				case 'report':
					return 'Reports';
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
			$matches = $this->queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public function getReactionsByAuthor($author, $type = '*') {
			$query = "/reactions/{$type}[@author='{$author}']";
			$matches = $this->queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public function getReactionCountByPost($post_id, $type, $type_bin) {
			$query = "/reactions/{$type}[@post='{$post_id}' and @type='{$type_bin}']";
			$matches = $this->queryDocument($query);

			return count($matches);
		}

		public function getReactionAverageByPost($post_id, $type) {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = $this->queryDocument($query);

			$sum = 0;

			foreach ($matches as $match) {
				$class = '\\models\\'.Reactions::classify($match);
				$reaction = $class::createObjectFromElement($match);
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

		public function getAnswersByPost($post_id) {
			$query = "/answers/answer[@post='{$post_id}']";
			$matches = $this->queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public function getAnswersByAuthor($author) {
			$query = "/answers/answer[@author='{$author}']";
			$matches = $this->queryDocument($query);

			return new \models\ReactionList($matches);
		}
	}

	class Reports extends Reactions {
		protected const DOCUMENT_NAME = 'reports';

		public static function createObjectFromElement($element, $object = null) {
			if (!$object)
				$object = new Answer();

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
			$matches = $this->queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public function getReportsByAuthor($author) {
			$query = "/reports/report[@author='{$author}']";
			$matches = $this->queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public function getReportByPostIdAuthor($post_id, $author) {
			$query = "/reports/report[@post='{$post_id}' and @author='{$author}']";
			$match = $this->queryDocument($query)->item(0);

			return $this->createObjectFromElement($match);
		}
	}

	class ReactionList extends \IteratorIterator {

		public function current(): \models\Reaction {
			$element = parent::current();
			$class = '\\models\\'.Reactions::classify($element);
			return $class::createObjectFromElement($element);
		}
	}
?>