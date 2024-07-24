<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MovieView.php');

	class MovieController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'display':
					$mov = $_GET['id'] ?? 'm1';
					$tab = $_GET['tab'] ?? 'question';

					$view = new \views\MovieView($this->session, $mov, $tab);
					$view->render();
					break;
			}
		}
	}

	new MovieController();
?>