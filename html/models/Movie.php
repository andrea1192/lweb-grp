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

		public static function createMovieFromRequest($request) {
			$movie = new self();

			$movie->title = $request->title;
			$movie->year = $request->year;
			$movie->duration = $request->duration;
			$movie->summary = $request->summary;
			$movie->director = $request->director;
			$movie->writer = $request->writer;

			return $movie;
		}
	}

	class Request extends Movie {
		public $status = 'submitted';
	}
?>