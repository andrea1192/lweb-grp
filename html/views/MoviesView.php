<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/Movie.php');

	class MoviesView {
		public $movies;

		public function __construct() {

			$this->movies = \models\Movies::getMovies();
		}

		public function printTitle() {

			print("Movies - grp");

		}

		public function printList() {

			print('<div class="flex list">');

			foreach ($this->movies as $movie) {
				print(\views\Movie::generateCard($movie));
			}

			print('</div>');
		}
	}
?>