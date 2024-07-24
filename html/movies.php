<?php namespace controllers;

	require_once('views/MoviesView.php');

	new MoviesController();

	class MoviesController {

		public function __construct() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'list':
					$view = new \views\MoviesView();
					$view->render();
					break;
			}
		}
	}
?>