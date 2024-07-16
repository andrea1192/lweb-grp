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
?>