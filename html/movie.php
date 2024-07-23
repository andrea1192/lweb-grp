<?php namespace controllers;

	require_once('views/MovieView.php');

	new MovieController();

	class MovieController {

		public function __construct() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'display':
					$mov = $_GET['id'] ?? 'm1';
					$tab = $_GET['tab'] ?? 'question';

					$view = new \views\MovieView($mov, $tab);
					$view->render();
					break;
			}
		}
	}
?>