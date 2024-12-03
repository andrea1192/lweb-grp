<?php namespace controllers;

	require_once('AbstractController.php');

	class ReportsController extends AbstractController {

		public function route() {

			// Livello di privilegio richiesto: login eseguito
			if (!$this->session->isLoggedIn())
				header('Location: index.php');

			$view = new \views\ReportsView();
			$view->render();
		}
	}

	new ReportsController();
?>