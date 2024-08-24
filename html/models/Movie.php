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
	}

	class Request extends Movie {
		public $status;
	}
?>