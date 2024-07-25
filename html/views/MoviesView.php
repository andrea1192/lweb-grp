<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');

	class MoviesView extends AbstractView {
		public $movies;

		public function __construct($session) {
			parent::__construct($session);

			$this->movies = \models\Movies::getMovies();
		}

		public function printTitle() {

			print("Movies - grp");

		}

		public function printList() {

			print('<div class="flex list">');

			foreach ($this->movies as $movie) {
				$view = new \views\Movie($this->session, $movie);
				$view->renderCard();
			}

			print('</div>');
		}

		public function render() {
			require_once('templates/MoviesListTemplate.php');
		}
	}
?>