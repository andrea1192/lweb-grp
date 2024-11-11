<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/CollectionViews.php');

	class MoviesController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'list_movies':
					$view = new \views\MoviesView('movies');
					break;
				case 'list_requests':
					$view = new \views\MoviesView('requests');
					break;
			}

			$view->render();
		}
	}

	new MoviesController();
?>