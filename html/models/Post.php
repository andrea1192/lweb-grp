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
?>