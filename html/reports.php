<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/CollectionViews.php');

	class ReportsController extends AbstractController {

		public function route() {
			$view = new \views\ReportsView($this->session);
			$view->render();
		}
	}

	new ReportsController();
?>