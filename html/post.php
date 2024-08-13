<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/PostView.php');

	class PostController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';
			$post = $_GET['id'] ?? '';
			$tab = $_GET['tab'] ?? 'question';
			$movie = $_GET['movie'] ?? 'm1';

			switch ($action) {

				default:
				case 'display':
					$view = new \views\PostView($this->session, $post);
					$view->render();
					break;

				case 'edit':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\PostEditView($this->session, $post);
					$view->render();
					break;

				case 'create':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\PostCreateView($this->session, $tab, $movie);
					$view->render();
					break;
			}
		}
	}

	new PostController();
?>