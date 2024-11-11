<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/CollectionViews.php');

	class UsersController extends AbstractController {

		public function route() {
			$view = new \views\UsersView();
			$view->render();
		}
	}

	new UsersController();
?>