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

				case 'save':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_POST)) {

						$post = \models\Post::createPost($_POST['type']);
						$post->id = $_POST['id'];
						$post->author = $_POST['author'];
						$post->date = $_POST['date'];
						$post->title = $_POST['title'];
						$post->text = $_POST['text'];

						if (isset($_POST['movie'])) {
							$post->movie = $_POST['movie'];
							$mapper = ServiceLocator::resolve('posts');
							$redir = "movie.php?id={$post->movie}&tab={$_POST['type']}";
						} else {
							$post->request = $_POST['request'];
							$mapper = ServiceLocator::resolve('comments');
							$redir = "movie.php?id={$post->request}&tab={$_POST['type']}";
						}

						if (isset($_POST['rating']))
							$post->rating = $_POST['rating'];
						if (isset($_POST['featured']))
							$post->featured = $_POST['featured'];
						if (isset($_POST['featuredAnswer']))
							$post->featuredAnswer = $_POST['featuredAnswer'];
						if (isset($_POST['reputation']))
							$post->reputation = $_POST['reputation'];

						$mapper->save($post);
					}

					header("Location: $redir");
					break;

				case 'delete':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['type'])) {

						if ($_GET['type'] != 'comment') {
							$mapper = ServiceLocator::resolve('posts');
							$post = $mapper->getPostById($_GET['id']);
							$redir = "movie.php?id={$post->movie}&tab={$_GET['type']}";
						} else {
							$mapper = ServiceLocator::resolve('comments');
							$post = $mapper->getCommentById($_GET['id']);
							$redir = "movie.php?id={$post->request}&tab={$_GET['type']}";
						}

						$mapper->delete($post->id);
					}

					header("Location: $redir");
					break;

				case 'answer':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ReactionCreateView($this->session, 'answer', $post);
					$view->render();
					break;

				case 'report':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ReactionCreateView($this->session, 'report', $post);
					$view->render();
					break;
			}
		}
	}

	new PostController();
?>