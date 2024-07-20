<?php namespace models;

	require_once('models/Reactions.php');

	abstract class Post {
		public $id;
		public $movie;
		public $author;
		public $date;

		public $title;
		public $text;

		public $reactions;

		public function __construct($element) {

			$this->id = $element->getAttribute('id');
			$this->movie = $element->getAttribute('movie');
			$this->author = $element->getAttribute('author');
			$this->date = $element->getAttribute('date');

			$this->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$this->text = $element->getElementsByTagName('text')->item(0)->textContent;
		}

		public static function generatePost($element) {

			switch ($element->nodeName) {
				case 'review':
					return new Review($element);
				case 'question':
					return new Question($element);
				case 'spoiler':
					return new Spoiler($element);
				case 'extra':
					return new Extra($element);
			}
		}
	}

	abstract class RatedPost extends Post {
		public $rating;

		public function __construct($element) {
			parent::__construct($element);

			$this->rating = $element->getElementsByTagName('rating')->item(0)->textContent;
		}
	}

	class Review extends RatedPost {

		public function __construct($element) {
			parent::__construct($element);

			$this->reactions = [
				'like' => new \models\BinaryReactionType($this->id, 'like', 'like', 'dislike')
			];
		}
	}

	class Question extends Post {
		public $featured;
		public $featuredAnswer;
		public $answers;

		public function __construct($element) {
			parent::__construct($element);

			$this->featured = (boolean) $element->getAttribute('featured');
			$this->featuredAnswer = (string) $element->getAttribute('featuredAnswer');

			$this->answers = \models\Answers::getAnswersByPost($this->id);
			$this->reactions = [
				'usefulness' => new \models\NumericReactionType($this->id, 'usefulness'),
				'agreement' => new \models\NumericReactionType($this->id, 'agreement')
			];
		}
	}

	class Spoiler extends RatedPost {

		public function __construct($element) {
			parent::__construct($element);

			$this->reactions = [
				'spoilage' => new \models\NumericReactionType($this->id, 'spoilage')
			];
		}
	}

	class Extra extends Post {
		public $reputation;

		public function __construct($element) {
			parent::__construct($element);

			$this->reputation = $element->getAttribute('reputation');
		}
	}
?>