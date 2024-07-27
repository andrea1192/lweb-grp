<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MoviesView.php');

	class MoviesController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'list_movies':
					$view = new \views\MoviesView($this->session);
					break;
				case 'list_requests':
					$view = new \views\RequestsView($this->session);
					break;
			}

			$view->render();
		}
	}

	new MoviesController();
?>