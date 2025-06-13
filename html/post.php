<?php namespace controllers;

	require_once('AbstractController.php');

	class PostController extends AbstractController {

		public function route() {
			$action = static::sanitize($_REQUEST['action'] ?? '');
			$post_id = static::sanitize($_GET['id'] ?? '');
			$post_type = static::sanitize($_GET['type'] ?? $_POST['type'] ?? '');

			switch ($action) {

				default:
				case 'display':
					$view = new \views\PostView($post_id);
					$view->render();
					break;

				case 'edit':
					$view = new \views\PostEditView($post_id);
					$view->render();
					break;

				case 'compose':
					if (isset($_GET['movie'])) {
						$movie_ref = static::sanitize($_GET['movie']);

						$view = new \views\PostComposeView($post_type, $movie_ref);
						$view->render();
					}
					break;

				case 'create':
				case 'update':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					// Controlla che la richiesta utilizzi il metodo HTTP POST
					static::checkPOST();

					$repo = ServiceLocator::resolve($post_type.'s');

					$state['id'] = $post_id;
					$state['status'] = static::sanitize($_POST['status'] ?? 'active');
					$state['author'] = static::sanitize($_POST['author'] ?? '');
					$state['text'] = static::sanitize($_POST['text']);

					$state['title'] =
							static::sanitize($_POST['title'] ?? '');
					$state['rating'] =
							static::sanitize($_POST['rating'] ?? '');
					$state['featured'] =
							(static::sanitize($_POST['featured'] ?? '') == 'true');
					$state['featuredAnswer'] =
							static::sanitize($_POST['featuredAnswer'] ?? '');
					$state['reputation'] =
							static::sanitize($_POST['reputation'] ?? '');

					switch ($post_type) {
						default:
						case 'review':
						case 'question':
						case 'spoiler':
						case 'extra':
							$state['movie'] = static::sanitize($_POST['movie']);
							$redir = "movie.php?id={$state['movie']}&type=movie&tab={$post_type}";
							break;

						case 'comment':
							$state['movie'] = static::sanitize($_POST['movie']);
							$redir = "movie.php?id={$state['movie']}&type=request&tab=comment";
							break;

						case 'answer':
							$state['post'] = static::sanitize($_POST['post']);
							$state['movie'] = static::sanitize($_POST['movie']);
							$redir = "movie.php?id={$state['movie']}&type=movie&tab=question";
							break;
					}

					// Porta a termine l'operazione corretta (create/update)
					try {
						if ($action == 'create') {
							$object = $repo->create($post_type, $state);
						} else {
							$object = \models\AbstractModel::build($post_type, $state);
							$repo->update($object);
						}
					} catch (\models\InvalidDataException $e) {
						static::abort(
								'Couldn\'t complete operation. Invalid or missing data.',
								$e->getErrors()
						);
					} catch (\mysqli_sql_exception $e) {
						static::abort(
								"Couldn't complete operation. Database error: {$e->getMessage()}"
						);
					}

					// Aggiorna e reindirizza l'utente
					if ($action == 'create') {
						$this->session->pushNotification('Post successfully created.');
					} else {
						$this->session->pushNotification('Post successfully updated.');
					}
					header("Location: $redir");
					break;

				case 'delete':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					$repo = ServiceLocator::resolve($post_type.'s');
					$post = $repo->read($post_id);

					$post->setStatus('deleted'); // soft-delete
					$repo->update($post);

					switch ($post_type) {
						default:
							$redir = "movie.php?id={$post->movie}&type=movie&tab={$post_type}";
							break;
						case 'comment':
							$redir = "movie.php?id={$post->movie}&type=request&tab=comment";
							break;
					}

					$this->session->pushNotification('Post deleted.');
					header("Location: $redir");
					break;

				case 'add_reaction':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					$reaction_type = $post_type;
					$post_type = \models\AbstractModel::getType($post_id);

					$posts = ServiceLocator::resolve($post_type.'s');
					$users = ServiceLocator::resolve('users');

					$state['author'] = $this->session->getUsername();
					$state['post'] = $post_id;
					$state['type'] = $reaction_type;
					$state['rating'] = static::sanitize($_POST['rating'] ?? '');

					// 'dislike' tipo speciale di 'like' (reazione binaria)
					// $reaction_type può essere aggiornato in quanto ora è preservato in $state
					if ($reaction_type == 'dislike')
						$reaction_type = 'like';

					$repo = ($reaction_type != 'usefulness') ? $reaction_type.'s' : $reaction_type.'es';
					$reactions = ServiceLocator::resolve($repo);
					$reaction_old =
							$reactions->getReaction($state['post'], $state['author']);

					if (!$reaction_old) {
							$reaction = $reactions->create($reaction_type, $state);
						} else {
							$reaction = \models\AbstractModel::build($reaction_type, $state);
							$reactions->update($reaction);
						}

					// Determina l'autore del post
					$post_author = $posts->read($post_id)->author;
					$post_user = $users->read($post_author);

					// Aggiorna la reputazione dell'autore del post
					switch ($reaction_type) {
						case 'like':
						case 'dislike':
							if ($reaction)
								$post_user->reputation +=
										$reaction::REPUTATION_DELTAS[$reaction->type];

							if ($reaction_old)
								$post_user->reputation -=
										$reaction::REPUTATION_DELTAS[$reaction_old->type];
							break;

						case 'usefulness':
						case 'agreement':
						case 'spoilage':
							if ($reaction)
								$post_user->reputation +=
										$reaction::REPUTATION_DELTAS[$reaction->rating];

							if ($reaction_old)
								$post_user->reputation -=
										$reaction::REPUTATION_DELTAS[$reaction_old->rating];
							break;
					}

					ServiceLocator::resolve('users')->update($post_user);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'answer':
					$view = new \views\ReactionCreateView('answer', $post_id);
					$view->render();
					break;

				case 'report':
					$view = new \views\ReactionCreateView('report', $post_id);
					$view->render();
					break;

				case 'send_report':
				case 'close_report':
					// Livello di privilegio richiesto per inviare report: 1 (utente)
					if ($action == 'send_report' && !$this->session->isAllowed())
						header('Location: index.php');

					// Livello di privilegio richiesto per gestire report: 2 (mod)
					if ($action == 'close_report' && !$this->session->isMod())
						header('Location: index.php');

					$repo = ServiceLocator::resolve('reports');
					$users = ServiceLocator::resolve('users');

					$state['post'] = $post_id;
					$state['author'] =
							static::sanitize($_POST['author'] ?? $this->session->getUsername());
					$state['status'] = static::sanitize($_POST['status'] ?? 'open');
					$state['message'] = static::sanitize($_POST['message']);
					$state['response'] = static::sanitize($_POST['response']);

					$reaction_old =
							$repo->getReaction($state['post'], $state['author']);

					// Porta a termine l'operazione corretta (send/close)
					if ($action == 'send_report' && !$reaction_old) {
						$report = $repo->create('report', $state);
						$redir = "post.php?id={$state['post']}";
					} else {
						$report = \models\AbstractModel::build($post_type, $state);
						$repo->update($report);
						$redir = $_SERVER['HTTP_REFERER'];
					}

					$author = $users->read($report->author);

					if ($report->status == 'accepted')
						$author->reputation += $report::REPUTATION_DELTAS[$report->status];
					elseif ($report->status == 'rejected')
						$author->reputation += $report::REPUTATION_DELTAS[$report->status];

					$users->update($author);

					// Aggiorna e reindirizza l'utente
					if ($action == 'send_report') {
						$this->session->pushNotification('Report sent. Thank you.');
					} else {
						$this->session->pushNotification('Report successfully updated.');
					}
					header("Location: $redir");
					break;

				case 'elevate':
					// Livello di privilegio richiesto: 2 (moderatore)
					if (!$this->session->isMod())
						header('Location: index.php');

					$repo = ServiceLocator::resolve('questions');
					$post = $repo->read($post_id);

					$post->setFeatured(true);
					$repo->update($post);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'select_answer':
					// Livello di privilegio richiesto: 2 (moderatore)
					if (!$this->session->isMod())
						header('Location: index.php');

					$answer_id = static::sanitize($_GET['answer']);

					$repo = ServiceLocator::resolve('questions');
					$post = $repo->read($post_id);

					$post->setFeaturedAnswer($answer_id);
					$repo->update($post);

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}
	}

	new PostController();
?>