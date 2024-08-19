<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/PostView.php');

	class PostController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';
			$post = $_GET['id'] ?? '';
			$tab = $_GET['tab'] ?? 'question';
			$movie_ref = $_GET['movie'] ?? 'm1';
			$post_ref = $_GET['post'] ?? 'q1';

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
					$view = new \views\PostCreateView($this->session, $tab, $movie_ref);
					$view->render();
					break;

				case 'answer':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ReactionCreateView($this->session, 'answer', $post_ref);
					$view->render();
					break;

				case 'report':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ReactionCreateView($this->session, 'report', $post_ref);
					$view->render();
					break;
			}
		}
	}

	new PostController();
?>