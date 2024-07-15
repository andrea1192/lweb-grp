<?php namespace models;

	class Post {
		public $id;
		public $movie;
		public $author;
		public $date;

		public $title;
		public $text;

		public function __construct($element) {

			$this->id = $element->getAttribute('id');
			$this->movie = $element->getAttribute('movie');
			$this->author = $element->getAttribute('author');
			$this->date = $element->getAttribute('date');

			$this->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$this->text = $element->getElementsByTagName('text')->item(0)->textContent;
		}
	}
?>