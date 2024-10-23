<?php namespace models;

	require_once('models/Posts.php');

	class Movie {
		public const ID_PREFIX = 'm';

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

		public static function getType($id) {
			preg_match('/([[:alpha:]]+)([[:digit:]])/', $id, $matches);

			$prefix = $matches[1];
			$number = $matches[2];

			switch ($prefix) {
				case Movie::ID_PREFIX:
					return 'movie';
				case Request::ID_PREFIX:
					return 'request';
			}
		}
	}

	class Request extends Movie {
		public const ID_PREFIX = 'req';
		public const REPUTATION_DELTAS = [
			'accepted' => +10
		];

		public $status = 'submitted';
		public $author;
	}
?>