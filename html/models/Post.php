<?php namespace models;

	require_once('models/AbstractModel.php');
	require_once('models/Reactions.php');

	abstract class Post extends AbstractModel {
		public $id;
		public $status = 'active';
		public $movie;
		public $author;
		public $date;
		public $title = '';
		public $text = '';

		public $reactions;

		public function __construct($state = null) {
			if (!$state)
				return;

			$this->id = $state['id'];
			$this->status = $state['status'];
			$this->movie = $state['movie'];
			$this->author = $state['author'];
			$this->date = $state['date'];
			$this->title = $state['title'];
			$this->text = $state['text'];
		}

		public function setStatus($status) {
			switch ($status) {
				case 'active':
				case 'deleted':
					$this->status = $status;
					break;
			}
		}
	}

	abstract class RatedPost extends Post {
		public $rating;

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->rating = $state['rating'];
		}
	}

	class Comment extends RatedPost {
		public const ID_PREFIX = 'c';

		public $request;

		public function __construct($state = null) {
			if (!$state)
				return;

			$this->id = $state['id'];
			$this->status = $state['status'];
			$this->request = $state['request'];
			$this->author = $state['author'];
			$this->date = $state['date'];
			$this->title = $state['title'];
			$this->rating = $state['rating'];
			$this->text = $state['text'];
		}
	}

	class Review extends RatedPost {
		public const ID_PREFIX = 'r';

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->reactions = [
				'like' => new \models\BinaryReactionType($state['id'], 'like', 'like', 'dislike')
			];
		}
	}

	class Question extends Post {
		public const ID_PREFIX = 'q';

		public $featured;
		public $featuredAnswer;

		public $answers;

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->featured = $state['featured'];
			$this->featuredAnswer = $state['featuredAnswer'];

			$this->answers =
					\controllers\ServiceLocator::resolve('answers')
					->getAnswersByPost($state['id']);

			$this->reactions = [
				'usefulness' => new \models\NumericReactionType($state['id'], 'usefulness'),
				'agreement' => new \models\NumericReactionType($state['id'], 'agreement')
			];
		}

		public function setFeatured($state) {
			$this->featured = $state;
		}

		public function setFeaturedAnswer($id) {
			$this->featuredAnswer = $id;
		}
	}

	class Spoiler extends RatedPost {
		public const ID_PREFIX = 's';

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->reactions = [
				'spoilage' => new \models\NumericReactionType($state['id'], 'spoilage')
			];
		}
	}

	class Extra extends Post {
		public const ID_PREFIX = 'e';

		public $reputation;

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->reputation = $state['reputation'];
		}
	}
?>