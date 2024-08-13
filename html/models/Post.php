<?php namespace models;

	require_once('models/Reactions.php');

	abstract class Post {
		public $id;
		public $movie;
		public $author;
		public $date;

		public $title = '';
		public $text = '';

		public $reactions;

		public function __construct($element = null) {

			if ($element)
				static::loadXML($element);
		}

		protected function loadXML($element) {
			$this->id = $element->getAttribute('id');
			$this->movie = $element->getAttribute('movie');
			$this->author = $element->getAttribute('author');
			$this->date = $element->getAttribute('date');

			$this->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$this->text = $element->getElementsByTagName('text')->item(0)->textContent;
		}

		public static function generatePost($element) {

			switch ($element->nodeName) {
				case 'comment':
					return new Comment($element);
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

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->rating = $element->getElementsByTagName('rating')->item(0)->textContent;
		}
	}

	class Comment extends RatedPost {
		public $request;

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->request = $element->getAttribute('request');
		}
	}

	class Review extends RatedPost {

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->reactions = [
				'like' => new \models\BinaryReactionType($this->id, 'like', 'like', 'dislike')
			];
		}
	}

	class Question extends Post {
		public $featured;
		public $featuredAnswer;
		public $answers;

		protected function loadXML($element) {
			parent::loadXML($element);

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

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->reactions = [
				'spoilage' => new \models\NumericReactionType($this->id, 'spoilage')
			];
		}
	}

	class Extra extends Post {
		public $reputation;

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->reputation = $element->getAttribute('reputation');
		}
	}
?>