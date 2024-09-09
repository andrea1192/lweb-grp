<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class ProfileController extends AbstractController {

		public function route() {
			$view = new \views\ProfileView($this->session);
			$view->render();
		}
	}

	new ProfileController();
?>