<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MoviesView.php');

	class MoviesController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'list':
					$view = new \views\MoviesView($this->session);
					$view->render();
					break;
			}
		}
	}

	new MoviesController();
?>