<?php namespace controllers;

	require_once('AbstractController.php');

	class MovieController extends AbstractController {

		public function route() {
			$action = static::sanitize($_REQUEST['action'] ?? '');
			$movie_id = static::sanitize($_GET['id'] ?? '');
			$movie_type = static::sanitize($_REQUEST['type'] ?? '');

			switch ($action) {

				default:
				case 'display':
					$view = new \views\MovieView($movie_id, $movie_type);
					$view->render();
					break;

				case 'edit':
					$view = new \views\MovieEditView($movie_id);
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

					if (isset($_POST)) {
						$repo = ServiceLocator::resolve('movies');

						$state['id'] = $movie_id;
						$state['title'] = static::sanitize($_POST['title']);
						$state['year'] = static::sanitize($_POST['year']);
						$state['duration'] = static::sanitize($_POST['duration']);
						$state['summary'] = static::sanitize($_POST['summary']);
						$state['director'] = static::sanitize($_POST['director']);
						$state['writer'] = static::sanitize($_POST['writer']);

						$state['status'] = static::sanitize($_POST['status'] ?? 'submitted');
						$state['author'] = static::sanitize($_POST['author'] ?? '');

						// Porta a termine l'operazione corretta (create/update)
						try {
							if ($action == 'create') {
								$object = $repo->create($movie_type, $state);
							} else {
								$object = \models\AbstractModel::build($movie_type, $state);
								$repo->update($object);
							}
						} catch (\Exception $e) {
							static::abort(
									'Couldn\'t complete operation. Invalid or missing data.',
									$e->getErrors()
							);
						}

						// Gestisce il caricamento di poster (locandine) o backdrop (sfondi)
						if (($_FILES['poster']['size'] !== 0)
									&& ($_FILES['poster']['type'] === $repo::MEDIA_TYPE)) {

							$ext = $repo::MEDIA_EXT;
							$dir = $repo::POSTERS_PATH;
							$name = $dir.$object->id.$ext;

							move_uploaded_file($_FILES['poster']['tmp_name'], $name);
						}
						if (($_FILES['backdrop']['size'] !== 0)
									&& ($_FILES['backdrop']['type'] === $repo::MEDIA_TYPE)) {

							$ext = $repo::MEDIA_EXT;
							$dir = $repo::BACKDROPS_PATH;
							$name = $dir.$object->id.$ext;

							move_uploaded_file($_FILES['backdrop']['tmp_name'], $name);
						}
					}

					// Aggiorna e reindirizza l'utente
					if ($action == 'create') {
						$this->session->pushNotification('Request submitted for approval.');
					} else {
						$this->session->pushNotification('Request successfully updated.');
					}
					$nextView = \views\Movie::matchModel($object);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'accept':
					// Livello di privilegio richiesto: 3 (admin)
					if (!$this->session->isAdmin())
						header('Location: index.php');

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
						$movie = $movies->create('movie', $state);

						$request = \models\AbstractModel::build('request', $state);
						$request = $movies->update($request);

					} catch (\Exception $e) {
						static::abort(
								'Couldn\'t complete operation. Invalid or missing data.',
								$e->getErrors()
						);
					}

					// Aggiorna la reputazione del proponente della scheda
					$author = $users->read($request->author);
					$author->reputation += $request::REPUTATION_DELTAS[$request->status];
					$users->update($author);

					// Gestisce il caricamento o la copia di poster (locandine)
					$ext = $movies::MEDIA_EXT;
					$dir = $movies::POSTERS_PATH;
					$req_name = $dir.$request->id.$ext;
					$mov_name = $dir.$movie->id.$ext;

					if (($_FILES['poster']['size'] !== 0)
								&& ($_FILES['poster']['type'] === $movies::MEDIA_TYPE)) {

						move_uploaded_file($_FILES['poster']['tmp_name'], $mov_name);
						copy($mov_name, $req_name);

					} elseif (file_exists($req_name)) {
						copy($req_name, $mov_name);
					}

					// Gestisce il caricamento o la copia di backdrop (sfondi)
					$ext = $movies::MEDIA_EXT;
					$dir = $movies::BACKDROPS_PATH;
					$req_name = $dir.$request->id.$ext;
					$mov_name = $dir.$movie->id.$ext;

					if (($_FILES['backdrop']['size'] !== 0)
								&& ($_FILES['backdrop']['type'] === $movies::MEDIA_TYPE)) {

						move_uploaded_file($_FILES['backdrop']['tmp_name'], $mov_name);
						copy($mov_name, $req_name);

					} elseif (file_exists($req_name)) {
						copy($req_name, $mov_name);
					}

					// Aggiorna e reindirizza l'utente
					$this->session->pushNotification('Movie successfully added to the archive.');
					$nextView = \views\Movie::matchModel($movie);
					header("Location: {$nextView->generateURL()}");
					break;

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
					header("Location: {$nextView->generateURL()}");
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