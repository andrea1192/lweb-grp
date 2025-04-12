<?php namespace controllers;

	require_once('AbstractController.php');

	class MovieController extends AbstractController {

		public function route() {
			$action = static::sanitize($_REQUEST['action'] ?? '');
			$movie_id = static::sanitize($_GET['id'] ?? '');
			$movie_type = static::sanitize($_REQUEST['type'] ?? '');
			$tab = static::sanitize($_REQUEST['tab'] ?? '');

			switch ($action) {

				default:
				case 'display':
					$view = new \views\MovieView($movie_id, $movie_type, $tab);
					$view->render();
					break;

				case 'edit':
					$view = new \views\MovieEditView($movie_id, $movie_type);
					$view->render();
					break;

				case 'compose':
					$view = new \views\MovieComposeView();
					$view->render();
					break;

				case 'create':
				case 'update':
					// Livello di privilegio richiesto per inviare richieste: 1 (utente)
					if ($action == 'create' && !$this->session->isAllowed())
						header('Location: index.php');

					// Livello di privilegio richiesto per modificare richieste: 2 (mod)
					if ($action == 'update' && !$this->session->isMod())
						header('Location: index.php');

					// Controlla che la richiesta utilizzi il metodo HTTP POST
					static::checkPOST();

					switch ($_POST['status'] ?? '') {
						default:
						case 'submitted':
						case 'rejected':
						case 'deleted':
							$repo = ServiceLocator::resolve('requests');
							break;
						case 'accepted':
							$repo = ServiceLocator::resolve('movies');
							break;
					}

					$state['id'] = $movie_id;
					$state['title'] = static::sanitize($_POST['title']);
					$state['year'] = static::sanitize($_POST['year']);
					$state['duration'] = static::sanitize($_POST['duration']);
					$state['summary'] = static::sanitize($_POST['summary']);
					$state['director'] = static::sanitize($_POST['director']);
					$state['writer'] = static::sanitize($_POST['writer']);

					$state['status'] = static::sanitize($_POST['status'] ?? 'submitted');
					$state['author'] = static::sanitize($_POST['author'] ?? '');

					$current_state = $repo->read($movie_id);

					if (($_FILES['poster']['size'] !== 0)
								&& (in_array($_FILES['poster']['type'], array_keys($repo::MEDIA_TYPES)))) {

						$state['poster'] = file_get_contents($_FILES['poster']['tmp_name']);

					} elseif ($current_state) {
						$state['poster'] = $current_state->poster;
					}

					if (($_FILES['backdrop']['size'] !== 0)
								&& (in_array($_FILES['backdrop']['type'], array_keys($repo::MEDIA_TYPES)))) {

						$state['backdrop'] = file_get_contents($_FILES['backdrop']['tmp_name']);

					} elseif ($current_state) {
						$state['backdrop'] = $current_state->backdrop;
					}

					// Porta a termine l'operazione corretta (create/update)
					try {
						if ($action == 'create') {
							$object = $repo->create($movie_type, $state);
						} else {
							$object = \models\AbstractModel::build($movie_type, $state);
							$repo->update($object);
						}
					} catch (\models\InvalidDataException $e) {
						static::abort(
								'Couldn\'t complete operation. Invalid or missing data.',
								$e->getErrors()
						);
					}

					// Aggiorna e reindirizza l'utente
					if ($action == 'create') {
						$this->session->pushNotification('Request submitted for approval.');
					} else {
						$this->session->pushNotification('Request successfully updated.');
					}
					$nextView = \views\Movie::matchModel($object);
					$redir = htmlspecialchars_decode($nextView->generateURL());
					header("Location: $redir");
					break;

				case 'accept':
					// Livello di privilegio richiesto: 3 (admin)
					if (!$this->session->isAdmin())
						header('Location: index.php');

					$requests = ServiceLocator::resolve('requests');
					$movies = ServiceLocator::resolve('movies');
					$users = ServiceLocator::resolve('users');

					$state['id'] = $movie_id;
					$state['status'] = 'accepted';
					$state['author'] = static::sanitize($_POST['author']);
					$state['title'] = static::sanitize($_POST['title']);
					$state['year'] = static::sanitize($_POST['year']);
					$state['duration'] = static::sanitize($_POST['duration']);
					$state['summary'] = static::sanitize($_POST['summary']);
					$state['director'] = static::sanitize($_POST['director']);
					$state['writer'] = static::sanitize($_POST['writer']);

					// Porta a termine l'operazione
					try {
						$request = \models\AbstractModel::build('request', $state);
						$requests->update($request);

					} catch (\InvalidDataException $e) {
						static::abort(
								'Couldn\'t complete operation. Invalid or missing data.',
								$e->getErrors()
						);
					}

					// Aggiorna la reputazione del proponente della scheda
					$author = $users->read($request->author);
					$author->reputation += $request::REPUTATION_DELTAS[$request->status];
					$users->update($author);

					// TODO: Unisci alla logica di gestione di 'update'

					// Aggiorna e reindirizza l'utente
					$this->session->pushNotification('Movie successfully added to the archive.');
					$newMovie = $movies->read($movie_id);
					$nextView = \views\Movie::matchModel($newMovie);
					$redir = htmlspecialchars_decode($nextView->generateURL());
					header("Location: $redir");
					return;

				case 'reject':
					// Livello di privilegio richiesto: 3 (admin)
					if (!$this->session->isAdmin())
						header('Location: index.php');

					$request_id = $movie_id;

					$requests = ServiceLocator::resolve('requests');
					$request = $requests->read($request_id);

					$request->setStatus('rejected');
					$requests->update($request);

					$nextView = \views\Movie::matchModel($request);
					$redir = htmlspecialchars_decode($nextView->generateURL());
					header("Location: $redir");
					break;

				case 'delete':
					// Livello di privilegio richiesto: 3 (admin)
					if (!$this->session->isAdmin())
						header('Location: index.php');

					$request_id = $movie_id;

					$requests = ServiceLocator::resolve('requests');
					$request = $requests->read($request_id);

					$request->setStatus('deleted'); // soft-delete
					$requests->update($request);

					header('Location: movies.php?action=list_requests');
					break;
			}
		}
	}

	new MovieController();
?>