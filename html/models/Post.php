<?php namespace models;

	require_once('models/Reactions.php');

	abstract class Post {
		public $id;
		public $status = 'active';
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

		public static function getType($id) {
			preg_match('/([[:alpha:]]+)([[:digit:]])/', $id, $matches);

			$prefix = $matches[1];
			$number = $matches[2];

			switch ($prefix) {
				case Comment::ID_PREFIX:
					return 'comment';
				case Review::ID_PREFIX:
					return 'review';
				case Question::ID_PREFIX:
					return 'question';
				case Spoiler::ID_PREFIX:
					return 'spoiler';
				case Extra::ID_PREFIX:
					return 'extra';
			}
		}
	}

	abstract class RatedPost extends Post {
		public $rating;
	}

	class Comment extends RatedPost {
		public const ID_PREFIX = 'c';

		public $request;
	}

	class Review extends RatedPost {
		public const ID_PREFIX = 'r';
	}

	class Question extends Post {
		public const ID_PREFIX = 'q';

		public $featured;
		public $featuredAnswer;
		public $answers;
	}

	class Spoiler extends RatedPost {
		public const ID_PREFIX = 's';
	}

	class Extra extends Post {
		public const ID_PREFIX = 'e';

		public $reputation;
	}


	/*class DeletedPost extends Post {}*/
?>