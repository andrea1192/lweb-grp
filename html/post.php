<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/PostView.php');

	class PostController extends AbstractController {

		public function route() {
			$post_id = static::sanitize($_GET['id'] ?? '');
			$post_type = static::sanitize($_GET['type'] ?? $_POST['type'] ?? '');

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
					if (isset($_GET['movie'])) {
						$movie_ref = static::sanitize($_GET['movie']);

						$view = new \views\PostCreateView($this->session, $post_type, $movie_ref);
						$view->render();
					}
					break;

				case 'save':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_POST)) {
						$post = \models\Post::createPost($post_type);
						$post->id = static::sanitize($_POST['id']);
						$post->author = static::sanitize($_POST['author']);
						$post->date = static::sanitize($_POST['date']);
						$post->title = static::sanitize($_POST['title']);
						$post->text = static::sanitize($_POST['text']);

						if (isset($_POST['movie'])) {
							$post->movie = static::sanitize($_POST['movie']);
							$mapper = ServiceLocator::resolve('posts');
							$redir = "movie.php?id={$post->movie}&type={$post_type}";
						} else {
							$post->request = static::sanitize($_POST['request']);
							$mapper = ServiceLocator::resolve('comments');
							$redir = "movie.php?id={$post->request}&type={$post_type}";
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
					if ($post_type != 'comment') {
						$mapper = ServiceLocator::resolve('posts');
						$post = $mapper->getPostById($post_id);
						$redir = "movie.php?id={$post->movie}&type={$post_type}";
					} else {
						$mapper = ServiceLocator::resolve('comments');
						$post = $mapper->getCommentById($post_id);
						$redir = "movie.php?id={$post->request}&type={$post_type}";
					}

					$mapper->delete($post->id);

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

				case 'elevate':
					$mapper = ServiceLocator::resolve('posts');
					$post = $mapper->getPostById($post_id);

					$post->featured = true;
					$mapper->save($post);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'select_answer':
					$answer_id = static::sanitize($_GET['answer']);

					$mapper = ServiceLocator::resolve('posts');
					$post = $mapper->getPostById($post_id);

					$post->featuredAnswer = $answer_id;
					$mapper->save($post);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}
	}

	new PostController();
?>