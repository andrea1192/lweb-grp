<?php namespace models;

	abstract class Reaction {
		public $post;
		public $author;

		public function __construct($element = null) {

			if ($element)
				static::loadXML($element);
		}

		protected function loadXML($element) {

			$this->post = $element->getAttribute('post');
			$this->author = $element->getAttribute('author');
		}

		public static function generateReaction($element) {

			switch ($element->nodeName) {
				case 'answer':
					return new Answer($element);
				case 'like':
					return new Like($element);
				case 'usefulness':
					return new Usefulness($element);
				case 'agreement':
					return new Agreement($element);
				case 'spoilage':
					return new Spoilage($element);
			}
		}
	}

	abstract class NumericRating extends Reaction {
		public $rating;

		public function loadXML($element) {
			parent::loadXML($element);

			$this->rating = $element->getAttribute('rating');
		}
	}

	class Answer extends Reaction {
		public $id;
		public $date;

		public $text = '';

		public $reactions;

		public function __construct($element = null) {
			parent::__construct($element);

			if ($element)
				$this->reactions = [
					'usefulness' => new \models\NumericReactionType($this->id, 'usefulness'),
					'agreement' => new \models\NumericReactionType($this->id, 'agreement')
				];
		}

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->id = $element->getAttribute('id');
			$this->date = $element->getAttribute('date');

			$this->text = $element->getElementsByTagName('text')->item(0)->textContent;
		}
	}

	class Like extends Reaction {
		public $type;

		public function loadXML($element) {
			parent::loadXML($element);

			$this->type = $element->getAttribute('type');
		}
	}

	class Usefulness extends NumericRating {}

	class Agreement extends NumericRating {}

	class Spoilage extends NumericRating {}

	abstract class ReactionType {
		public $type;
		public $list;
	}

	class BinaryReactionType extends ReactionType {
		public $count_up;
		public $count_down;

		public function __construct($post_id, $reaction_type, $type_up, $type_down) {
			$this->type = $reaction_type;

			$this->list = \models\Reactions::getReactionsByPost($post_id);
			$this->count_up = \models\Reactions::getReactionCountByPost($post_id, $reaction_type, $type_up);
			$this->count_down = \models\Reactions::getReactionCountByPost($post_id, $reaction_type, $type_down);
		}
	}

	class NumericReactionType extends ReactionType {
		public $average;

		public function __construct($post_id, $reaction_type) {
			$this->type = $reaction_type;

			$this->list = \models\Reactions::getReactionsByPost($post_id);
			$this->average = \models\Reactions::getReactionAverageByPost($post_id, $reaction_type);
		}
	}
?>