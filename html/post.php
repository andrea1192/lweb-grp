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
					$view = new \views\PostEditView($this->session, $post_id);
					$view->render();
					break;

				case 'create':
					if (isset($_GET['movie'])) {
						$movie_ref = static::sanitize($_GET['movie']);

						$view = new \views\PostCreateView($this->session, $post_type, $movie_ref);
						$view->render();
					}
					break;

				case 'save':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_POST)) {

						switch ($post_type) {
							default:
							case 'review':
							case 'question':
							case 'spoiler':
							case 'extra':
								$post = \models\Post::createPost($post_type);
								$post->movie = static::sanitize($_POST['movie']);

								$mapper = ServiceLocator::resolve('posts');
								$redir = "movie.php?id={$post->movie}&type={$post_type}";
								break;

							case 'comment':
								$post = \models\Post::createPost($post_type);
								$post->request = static::sanitize($_POST['request']);

								$mapper = ServiceLocator::resolve('comments');
								$redir = "movie.php?id={$post->request}&type={$post_type}";
								break;

							case 'answer':
								$post = new \models\Answer();
								$post->post = static::sanitize($_POST['post']);

								$mapper = ServiceLocator::resolve('answers');
								$movie_id = ServiceLocator::resolve('posts')->getPostById($post->post)->movie;
								$redir = "movie.php?id={$movie_id}&type=question";
								break;
						}

						$post->id = static::sanitize($_POST['id']);
						$post->status = static::sanitize($_POST['status']);
						$post->author = static::sanitize($_POST['author']);
						$post->date = static::sanitize($_POST['date']);
						$post->text = static::sanitize($_POST['text']);

						if (empty($post->author))
							$post->author = ServiceLocator::resolve('session')->getUsername();

						if (empty($post->date))
							$post->date = date('c');

						if (isset($_POST['title']))
							$post->title = static::sanitize($_POST['title']);
						if (isset($_POST['rating']))
							$post->rating = static::sanitize($_POST['rating']);
						if (isset($_POST['featured']))
							$post->featured = (bool) (static::sanitize($_POST['featured']) == 'true');
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

					$post->status = 'deleted';
					$mapper->save($post);

					header("Location: $redir");
					break;

				case 'add_reaction':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					$reaction_type = $post_type;
					$reaction_author = ServiceLocator::resolve('session')->getUsername();

					$mapper = ServiceLocator::resolve('reactions');
					$reaction = \models\Reaction::createReaction($reaction_type);

					$reaction->post = $post_id;
					$reaction->author = $reaction_author;

					$post_author = ServiceLocator::resolve('posts')->select($post_id)->author;
					$post_user = ServiceLocator::resolve('users')->select($post_author);

					if (property_exists($reaction, 'type')) {
						$reaction->type = $reaction_type;
						$post_user->reputation += $reaction::REPUTATION_DELTAS[$reaction->type];

						if ($reaction_old = $mapper->getReaction($post_id, $reaction_author, 'like'))
							$post_user->reputation -= $reaction::REPUTATION_DELTAS[$reaction_old->type];
					}

					if (property_exists($reaction, 'rating')) {
						$reaction->rating = static::sanitize($_POST['rating']);
						$post_user->reputation += $reaction::REPUTATION_DELTAS[$reaction->rating];

						if ($reaction_old = $mapper->getReaction($post_id, $reaction_author, $reaction_type))
							$post_user->reputation -= $reaction::REPUTATION_DELTAS[$reaction_old->rating];
					}

					$mapper->save($reaction);
					ServiceLocator::resolve('users')->update($post_author, $post_user);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'answer':
					$view = new \views\ReactionCreateView($this->session, 'answer', $post_id);
					$view->render();
					break;

				case 'report':
					$view = new \views\ReactionCreateView($this->session, 'report', $post_id);
					$view->render();
					break;

				case 'send_report':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					$post = new \models\Report();
					$mapper = ServiceLocator::resolve('reports');

					$post->post = $post_id;
					$post->author = static::sanitize($_POST['author']);
					$post->date = static::sanitize($_POST['date']);
					$post->status = static::sanitize($_POST['status']);

					$post->message = static::sanitize($_POST['message']);
					$post->response = static::sanitize($_POST['response']);

					if (empty($post->author))
						$post->author = ServiceLocator::resolve('session')->getUsername();

					if (empty($post->date))
						$post->date = date('c');

					$author = ServiceLocator::resolve('users')->select($post->author);

					if ($post->status == 'accepted')
						$author->reputation += $post::REPUTATION_DELTAS[$post->status];
					elseif ($post->status == 'rejected')
						$author->reputation += $post::REPUTATION_DELTAS[$post->status];

					$mapper->save($post);
					ServiceLocator::resolve('users')->update($author->username, $author);

					$movie_id = ServiceLocator::resolve('posts')->getPostById($post->post)->movie;
					$redir = "movie.php?id={$movie_id}";
					header("Location: $redir");
					break;

				case 'elevate':
					// Livello di privilegio richiesto: 2 (moderatore)
					if (!$this->session->isMod())
						header('Location: index.php');

					$mapper = ServiceLocator::resolve('posts');
					$post = $mapper->getPostById($post_id);

					$post->featured = true;
					$mapper->save($post);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'select_answer':
					// Livello di privilegio richiesto: 2 (moderatore)
					if (!$this->session->isMod())
						header('Location: index.php');

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