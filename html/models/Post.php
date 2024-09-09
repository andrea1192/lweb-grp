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

		public static function createPost($type) {

			switch ($type) {
				case 'comment':
					return new \models\Comment();
				case 'review':
					return new \models\Review();
				case 'question':
					return new \models\Question();
				case 'spoiler':
					return new \models\Spoiler();
				case 'extra':
					return new \models\Extra();
			}
		}
	}

	abstract class RatedPost extends Post {
		public $rating;
	}

	class Comment extends RatedPost {
		public $request;
	}

	class Review extends RatedPost {
	}

	class Question extends Post {
		public $featured;
		public $featuredAnswer;
		public $answers;
	}

	class Spoiler extends RatedPost {
	}

	class Extra extends Post {
		public $reputation;
	}


	class DeletedPost extends Post {}
?>