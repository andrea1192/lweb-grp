<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/PostView.php');

	class PostController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';
			$post = $_GET['id'] ?? '';

			switch ($action) {

				default:
				case 'display':
					$view = new \views\PostView($this->session, $post);
					$view->render();
					break;

				case 'edit':
					$view = new \views\PostEditView($this->session, $post);
					$view->render();
					break;
			}
		}
	}

	new PostController();
?>