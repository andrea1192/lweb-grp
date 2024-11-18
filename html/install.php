<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class SetupController extends AbstractController {

		public function route() {
			$view = new \views\SetupView();
			$view->render();
		}
	}

	new SetupController();
?>