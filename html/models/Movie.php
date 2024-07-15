<?php namespace models;

	require_once('models/Posts.php');

	class Movie {
		public $id;

		public $title;
		public $year;
		public $duration;
		public $summary;
		public $director;
		public $writer;

		public $posts;

		public function __construct($element) {

			$this->id = $element->getAttribute('id');

			$this->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$this->year = $element->getElementsByTagName('year')->item(0)->textContent;
			$this->duration = $element->getElementsByTagName('duration')->item(0)->textContent;
			$this->summary = $element->getElementsByTagName('summary')->item(0)->textContent;
			$this->director = $element->getElementsByTagName('director')->item(0)->textContent;
			$this->writer = $element->getElementsByTagName('writer')->item(0)->textContent;

			$this->posts = \models\Posts::getPosts($this->id);
		}
	}
?>