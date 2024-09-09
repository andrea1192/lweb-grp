<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class LoginController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'signin':
					$view = new \views\SigninView($this->session);
					$view->render();
					break;

				case 'signup':
					$view = new \views\SignupView($this->session);
					$view->render();
					break;
			}
		}
	}

	new LoginController();
?>