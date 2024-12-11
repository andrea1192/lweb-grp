<?php namespace controllers;

	require_once('AbstractController.php');

	class ReportsController extends AbstractController {

		public function route() {

			// Livello di privilegio richiesto: login eseguito
			if (!$this->session->isLoggedIn())
				header('Location: index.php');

			// Azione di default: display (visualizza lista)
			$view = new \views\ReportsView();
			$view->render();

			// Per altre azioni relative alle segnalazioni, vd. post.php (send_report/close_report)
		}
	}

	new ReportsController();
?>