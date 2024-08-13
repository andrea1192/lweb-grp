<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');

	abstract class AbstractMoviesView extends AbstractView {
		protected const TITLE = '';

		public function printTitle() {
			$title = static::TITLE;

			print("{$title} - grp");
		}

		public function printActionButton() {

			if ($this->session->isAllowed())
				print(UIComponents::getFAB('Add movie', 'add', 'movie.php?action=create'));
		}

		public function render() {
			require_once('templates/MoviesListTemplate.php');
		}
	}

	class MoviesView extends AbstractMoviesView {
		protected const TITLE = 'Movies';
		public $movies;

		public function __construct($session) {
			parent::__construct($session);

			$this->movies = \models\Movies::getMovies();
		}

		public function printList() {
			$title = static::TITLE;

			print("<h1>{$title}</h1>");
			print('<div class="flex list">');

			foreach ($this->movies as $movie) {
				$view = new \views\Movie($this->session, $movie);
				$view->displayCard();
			}

			print('</div>');
		}
	}

	class RequestsView extends AbstractMoviesView {
		protected const TITLE = 'Requests';
		public $requests;

		public function __construct($session) {
			parent::__construct($session);

			$this->requests = \models\Requests::getRequests();
		}

		public function printList() {
			$title = static::TITLE;

			print("<h1>{$title}</h1>");
			print('<div class="flex list">');

			foreach ($this->requests as $request) {
				$view = new \views\Request($this->session, $request);
				$view->displayCard();
			}

			print('</div>');
		}
	}
?>