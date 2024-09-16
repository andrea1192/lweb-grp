<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/PostView.php');

	class PostController extends AbstractController {

		public function route() {
			if (!isset($_GET['id']))
				die('Post ID missing from query string');

			$post_id = static::sanitize($_GET['id']);

			switch ($_GET['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\PostView($this->session, $post_id);
					$view->render();
					break;

				case 'edit':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\PostEditView($this->session, $post_id);
					$view->render();
					break;

				case 'create':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['movie']) && isset($_GET['tab'])) {
						$movie_ref = static::sanitize($_GET['movie']);
						$type = static::sanitize($_GET['tab']);

						$view = new \views\PostCreateView($this->session, $type, $movie_ref);
						$view->render();
					}
					break;

				case 'save':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_POST)) {
						$type = static::sanitize($_POST['type']);

						$post = \models\Post::createPost($type);
						$post->id = static::sanitize($_POST['id']);
						$post->author = static::sanitize($_POST['author']);
						$post->date = static::sanitize($_POST['date']);
						$post->title = static::sanitize($_POST['title']);
						$post->text = static::sanitize($_POST['text']);

						if (isset($_POST['movie'])) {
							$post->movie = static::sanitize($_POST['movie']);
							$mapper = ServiceLocator::resolve('posts');
							$redir = "movie.php?id={$post->movie}&tab={$type}";
						} else {
							$post->request = static::sanitize($_POST['request']);
							$mapper = ServiceLocator::resolve('comments');
							$redir = "movie.php?id={$post->request}&tab={$type}";
						}

						if (empty($post->author))
							$post->author = ServiceLocator::resolve('session')->getUsername();

						if (empty($post->date))
							$post->date = date('c');

						if (isset($_POST['rating']))
							$post->rating = static::sanitize($_POST['rating']);
						if (isset($_POST['featured']))
							$post->featured = static::sanitize($_POST['featured']);
						if (isset($_POST['featuredAnswer']))
							$post->featuredAnswer = static::sanitize($_POST['featuredAnswer']);
						if (isset($_POST['reputation']))
							$post->reputation = static::sanitize($_POST['reputation']);

						$mapper->save($post);
					}

					header("Location: $redir");
					break;

				case 'delete':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['type'])) {
						$type = static::sanitize($_GET['type']);

						if ($type != 'comment') {
							$mapper = ServiceLocator::resolve('posts');
							$post = $mapper->getPostById($post_id);
							$redir = "movie.php?id={$post->movie}&tab={$type}";
						} else {
							$mapper = ServiceLocator::resolve('comments');
							$post = $mapper->getCommentById($post_id);
							$redir = "movie.php?id={$post->request}&tab={$type}";
						}

						$mapper->delete($post->id);
					}

					header("Location: $redir");
					break;

				case 'answer':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ReactionCreateView($this->session, 'answer', $post_id);
					$view->render();
					break;

				case 'report':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ReactionCreateView($this->session, 'report', $post_id);
					$view->render();
					break;
			}
		}
	}

	new PostController();
?>