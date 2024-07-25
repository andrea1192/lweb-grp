<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MovieView.php');

	class MovieController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'display':
					$movie_id = $_GET['id'] ?? 'm1';

					if (preg_match('/m[0-9]*/', $movie_id)) {

						$tab = $_GET['tab'] ?? 'question';

						$view = new \views\MovieView($this->session, $movie_id, $tab);
						$view->render();
					} else {

						$view = new \views\RequestView($this->session, $movie_id);
						$view->render();
					}

					break;
			}
		}
	}

	new MovieController();
?>