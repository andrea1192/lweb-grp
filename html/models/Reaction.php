<?php namespace models;

	abstract class Reaction {
		public $post;
		public $author;

		public static function createReaction($type) {

			switch ($type) {
				case 'answer':
					return new \models\Answer();
				case 'like':
				case 'dislike':
					return new \models\Like();
				case 'usefulness':
					return new \models\Usefulness();
				case 'agreement':
					return new \models\Agreement();
				case 'spoilage':
					return new \models\Spoilage();
				case 'report':
					return new \models\Report();
			}
		}
	}

	abstract class BinaryRating extends Reaction {
		public $type;
	}

	abstract class NumericRating extends Reaction {
		public $rating;
	}

	class Answer extends Reaction {
		public $id;
		public $date;
		public $text = '';

		public $reactions;
	}

	class Like extends BinaryRating {}

	class Usefulness extends NumericRating {}

	class Agreement extends NumericRating {}

	class Spoilage extends NumericRating {}

	class Report extends Reaction {
		public $date;
		public $message = '';
		public $response = '';
		public $status = 'open';
	}


	abstract class ReactionType {
		public $post;
		public $type;
		public $list;
	}

	class BinaryReactionType extends ReactionType {
		public $count_up;
		public $count_down;

		public function __construct($post_id, $reaction_type, $type_up, $type_down) {
			$this->post = $post_id;
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
			$this->post = $post_id;
			$this->type = $reaction_type;

			$reactions = \controllers\ServiceLocator::resolve('reactions');
			$this->list = $reactions->getReactionsByPost($post_id);
			$this->average = $reactions->getReactionAverageByPost($post_id, $reaction_type);
		}
	}
?>