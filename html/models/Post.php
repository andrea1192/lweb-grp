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

		protected function __construct($state) {
			parent::__construct($state);

			$this->id = $this->validateString('id');
			$this->status = $this->validateString('status');
			$this->movie = $this->validateString('movie');
			$this->author = $this->validateString('author');
			$this->date = $this->validateString('date');
			$this->title = $this->validateString('title');
			$this->text = $this->validateString('text');

			$this->checkValidation();
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

		protected function __construct($state) {
			parent::__construct($state);

			$this->rating = $this->validateNumeric('rating', min: 0, max: 10);

			$this->checkValidation();
		}
	}

	class Comment extends Post {
		public const ID_PREFIX = 'c';

		public $request;
		public $rating;

		protected function __construct($state) {
			$state['movie'] = $state['request'];
			parent::__construct($state);

			$this->request = $this->validateString('request');
			$this->rating = $this->validateString('rating');

			$this->checkValidation();
		}
	}

	class Review extends RatedPost {
		public const ID_PREFIX = 'r';

		protected function __construct($state) {
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

		protected function __construct($state) {
			parent::__construct($state);

			$this->featured = $this->validateString('featured', required: false);
			$this->featuredAnswer = $this->validateString('featuredAnswer', required: false);

			$this->answers =
					\controllers\ServiceLocator::resolve('answers')
					->getAnswersByPost($state['id']);

			$this->reactions = [
				'usefulness' => new \models\NumericReactionType($state['id'], 'usefulness'),
				'agreement' => new \models\NumericReactionType($state['id'], 'agreement')
			];

			$this->checkValidation();
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

		protected function __construct($state) {
			parent::__construct($state);

			$this->reactions = [
				'spoilage' => new \models\NumericReactionType($state['id'], 'spoilage')
			];
		}
	}

	class Extra extends Post {
		public const ID_PREFIX = 'e';

		public $reputation;

		protected function __construct($state) {
			parent::__construct($state);

			$this->reputation = $this->validateNumeric('reputation');

			$this->checkValidation();
		}
	}
?>