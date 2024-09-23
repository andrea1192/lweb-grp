<?php namespace models;

	abstract class Reaction {
		public $post;
		public $author;
		public $date;
	}

	abstract class NumericRating extends Reaction {
		public $rating;
	}

	class Answer extends Reaction {
		public $id;
		public $text = '';

		public $reactions;
	}

	class Like extends Reaction {
		public $type;
	}

	class Usefulness extends NumericRating {}

	class Agreement extends NumericRating {}

	class Spoilage extends NumericRating {}

	class Report extends Reaction {
		public $message = '';
		public $response = '';
		public $status = 'open';
	}


	abstract class ReactionType {
		public $type;
		public $list;
	}

	class BinaryReactionType extends ReactionType {
		public $count_up;
		public $count_down;

		public function __construct($post_id, $reaction_type, $type_up, $type_down) {
			$this->type = $reaction_type;

			$reactions = \controllers\ServiceLocator::resolve('reactions');
			$this->list =
					$reactions->getReactionsByPost($post_id);
			$this->count_up =
					$reactions->getReactionCountByPost($post_id, $reaction_type, $type_up);
			$this->count_down =
					$reactions->getReactionCountByPost($post_id, $reaction_type, $type_down);
		}
	}

	class NumericReactionType extends ReactionType {
		public $average;

		public function __construct($post_id, $reaction_type) {
			$this->type = $reaction_type;

			$reactions = \controllers\ServiceLocator::resolve('reactions');
			$this->list = $reactions->getReactionsByPost($post_id);
			$this->average = $reactions->getReactionAverageByPost($post_id, $reaction_type);
		}
	}
?>