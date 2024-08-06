<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MovieView.php');

	class MovieController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';
			$movie = $_GET['id'] ?? 'm1';
			$tab = $_GET['tab'] ?? 'question';

			switch ($action) {

				default:
				case 'display':
					$view = new \views\MovieView($this->session, $movie, $tab);
					$view->render();
					break;

				case 'edit':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\MovieEditView($this->session, $movie);
					$view->render();
			}
		}
	}

	new MovieController();
?>