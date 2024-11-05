<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/PostView.php');

	class PostController extends AbstractController {

		public function route() {
			$action = static::sanitize($_REQUEST['action'] ?? '');
			$post_id = static::sanitize($_GET['id'] ?? '');
			$post_type = static::sanitize($_GET['type'] ?? $_POST['type'] ?? '');

			switch ($action) {

				default:
				case 'display':
					$view = new \views\PostView($this->session, $post_id);
					$view->render();
					break;

				case 'edit':
					$view = new \views\PostEditView($this->session, $post_id);
					$view->render();
					break;

				case 'compose':
					if (isset($_GET['movie'])) {
						$movie_ref = static::sanitize($_GET['movie']);

						$view = new \views\PostComposeView($this->session, $post_type, $movie_ref);
						$view->render();
					}
					break;

				case 'create':
				case 'update':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					if (isset($_POST)) {
						$repo = ServiceLocator::resolve('posts');

						$state['id'] = $post_id;
						$state['status'] = static::sanitize($_POST['status'] ?? 'active');
						$state['author'] = static::sanitize($_POST['author'] ?? '');
						$state['date'] = static::sanitize($_POST['date'] ?? '');
						$state['text'] = static::sanitize($_POST['text']);

						$state['title'] = static::sanitize($_POST['title'] ?? '');
						$state['rating'] = static::sanitize($_POST['rating'] ?? '');
						$state['featured'] = (static::sanitize($_POST['featured'] ?? '') == 'true');
						$state['featuredAnswer'] = static::sanitize($_POST['featuredAnswer'] ?? '');
						$state['reputation'] = static::sanitize($_POST['reputation'] ?? '');

						switch ($post_type) {
							default:
							case 'review':
							case 'question':
							case 'spoiler':
							case 'extra':
								$state['movie'] = static::sanitize($_POST['movie']);
								$redir = "movie.php?id={$state['movie']}&type={$post_type}";
								break;

							case 'comment':
								$state['request'] = static::sanitize($_POST['request']);
								$redir = "movie.php?id={$state['request']}&type={$post_type}";
								break;

							case 'answer':
								$state['post'] = static::sanitize($_POST['post']);
								$redir = "movie.php?id={$repo->read($state['post'])->movie}&type=question";
								break;
						}

						// Porta a termine l'operazione corretta
						try {
							if ($action == 'create') {
								$object = $repo->create($post_type, $state);
							} else {
								$object = \models\AbstractModel::build($post_type, $state);
								$repo->update($object);
							}
						} catch (\Exception $e) {
							static::abort(
									'Couldn\'t complete operation. Invalid or missing data.',
									$e->getErrors()
							);
						}
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

					$repo = ServiceLocator::resolve('posts');
					$post = $repo->read($post_id);

					$post->setStatus('deleted');
					$repo->update($post);

					switch ($post_type) {
						default:
							$redir = "movie.php?id={$post->movie}&type={$post_type}";
							break;
						case 'comment':
							$redir = "movie.php?id={$post->request}&type={$post_type}";
							break;
					}

					header("Location: $redir");
					break;

				case 'add_reaction':
					// Livello di privilegio richiesto: 1 (utente registrato)
					if (!$this->session->isAllowed())
						header('Location: index.php');

					$repo = ServiceLocator::resolve('posts');
					$users = ServiceLocator::resolve('users');

					$state['author'] = ServiceLocator::resolve('session')->getUsername();
					$state['post'] = $post_id;
					$state['type'] = $post_type;
					$state['rating'] = static::sanitize($_POST['rating'] ?? '');

					if ($post_type == 'dislike')
						$post_type = 'like';

					$reaction_old =
							$repo->readReaction($state['post'], $state['author'], $post_type);

					if (!$reaction_old) {
							$reaction = $repo->create($post_type, $state);
						} else {
							$reaction = \models\AbstractModel::build($post_type, $state);
							$repo->update($reaction);
						}

					// Determina l'autore del post
					$post_author = $repo->read($post_id)->author;
					$post_user = $users->select($post_author);

					// Aggiorna la reputazione dell'autore del post
					switch ($post_type) {
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
					$state['author'] = static::sanitize($_POST['author'] ?? '');
					$state['date'] = static::sanitize($_POST['date'] ?? '');
					$state['status'] = static::sanitize($_POST['status'] ?? 'open');
					$state['message'] = static::sanitize($_POST['message']);
					$state['response'] = static::sanitize($_POST['response']);

					// Porta a termine l'operazione corretta
					if ($action == 'send_report') {
						$report = $repo->create('report', $state);
						$redir = "movie.php?id={$repo->read($report->post)->movie}";
					} else {
						$report = \models\AbstractModel::build($post_type, $state);
						$repo->update($report);
						$redir = $_SERVER['HTTP_REFERER'];
					}

					$author = $users->select($report->author);

					if ($report->status == 'accepted')
						$author->reputation += $report::REPUTATION_DELTAS[$report->status];
					elseif ($report->status == 'rejected')
						$author->reputation += $report::REPUTATION_DELTAS[$report->status];

					$users->update($author->username, $author);

					header("Location: $redir");
					break;

				case 'elevate':
					// Livello di privilegio richiesto: 2 (moderatore)
					if (!$this->session->isMod())
						header('Location: index.php');

					$repo = ServiceLocator::resolve('posts');
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

					$repo = ServiceLocator::resolve('posts');
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