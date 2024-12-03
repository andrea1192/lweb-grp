<?php namespace models;

	abstract class Reaction extends AbstractModel {
		public $post;
		public $author;

		protected function __construct($state) {
			parent::__construct($state);

			$this->post = $state['post'];
			$this->author = $state['author'];
		}
	}

	abstract class BinaryRating extends Reaction {
		public $type;

		protected function __construct($state) {
			parent::__construct($state);
			$this->type = $state['type'];
		}
	}

	abstract class NumericRating extends Reaction {
		public $rating;

		protected function __construct($state) {
			parent::__construct($state);
			$this->rating = $state['rating'];
		}
	}

	class Like extends BinaryRating {
		public const REPUTATION_DELTAS = [
			'like' => +1,
			'dislike' => -1
		];
	}

	class Usefulness extends NumericRating {
		public const REPUTATION_DELTAS = [
			1 => -2,
			2 => -1,
			3 => 0,
			4 => +1,
			5 => +2
		];
	}

	class Agreement extends NumericRating {
		public const REPUTATION_DELTAS = [
			1 => -2,
			2 => -1,
			3 => 0,
			4 => +1,
			5 => +2
		];
	}

	class Spoilage extends NumericRating {
		public const REPUTATION_DELTAS = [
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0,
			8 => 0,
			9 => 0,
			10 => 0
		];
	}

	class Answer extends Reaction {
		public const ID_PREFIX = 'a';

		public $id;
		public $status = 'active';
		public $date;
		public $text = '';

		public $reactions;

		protected function __construct($state) {
			parent::__construct($state);

			$this->id = $state['id'];
			$this->status = $state['status'];
			$this->date = $state['date'];
			$this->text = $state['text'];

			$this->reactions = [
					'usefulness' => new \models\NumericReactionType($state['id'], 'usefulness'),
					'agreement' => new \models\NumericReactionType($state['id'], 'agreement')
				];
		}
	}

	class Report extends Reaction {
		public const REPUTATION_DELTAS = [
			'accepted' => +5,
			'rejected' => -5
		];

		public $date;
		public $status = 'open';
		public $message = '';
		public $response = '';

		protected function __construct($state) {
			parent::__construct($state);

			$this->date = $state['date'];
			$this->status = $state['status'];
			$this->message = $state['message'];
			$this->response = $state['response'];
		}
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