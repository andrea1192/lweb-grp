<?php namespace models;

	abstract class Reaction {
		public $post;
		public $author;

		public $title;
		public $text;

		public function __construct($element) {

			$this->post = $element->getAttribute('post');
			$this->author = $element->getAttribute('author');
		}

		public static function generateReaction($element) {

			switch ($element->nodeName) {
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

		public function __construct($element) {
			parent::__construct($element);

			$this->rating = $element->getAttribute('rating');
		}
	}

	class Like extends Reaction {
		public $type;

		public function __construct($element) {
			parent::__construct($element);

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