<?php namespace models;

	require_once('models/Posts.php');

	class Movie {
		public $id;

		public $title = '';
		public $year = '';
		public $duration = '';
		public $summary = '';
		public $director = '';
		public $writer = '';

		public $posts;

		public function __construct($element = null) {

			if ($element)
				static::loadXML($element);
		}

		protected function loadXML($element) {

			$this->id = $element->getAttribute('id');

			$this->title = $element->getElementsByTagName('title')->item(0)->textContent;
			$this->year = $element->getElementsByTagName('year')->item(0)->textContent;

			$unavail = new class {public $textContent = 'N/A';};

			$this->duration =
				($element->getElementsByTagName('duration')->item(0) ?? $unavail)
				->textContent;

			$this->summary =
				($element->getElementsByTagName('summary')->item(0) ?? $unavail)
				->textContent;

			$this->director =
				($element->getElementsByTagName('director')->item(0) ?? $unavail)
				->textContent;

			$this->writer =
				($element->getElementsByTagName('writer')->item(0) ?? $unavail)
				->textContent;

			$this->posts = \models\Posts::getPostsByMovie($this->id);
		}
	}

	class Request extends Movie {
		public $status;

		protected function loadXML($element) {
			parent::loadXML($element);

			$this->status = $element->getAttribute('status');
			$this->posts = \models\Comments::getCommentsByRequest($this->id);
		}
	}
?>