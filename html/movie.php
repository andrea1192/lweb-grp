<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MovieView.php');

	class MovieController extends AbstractController {

		public function route() {
			$movie_id = static::sanitize($_GET['id'] ?? '');
			$tab = static::sanitize($_GET['type'] ?? 'question');

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\MovieView($this->session, $movie_id, $tab);
					$view->render();
					break;

				case 'edit':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\MovieEditView($this->session, $movie_id);
					$view->render();
					break;

				case 'create':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\MovieCreateView($this->session);
					$view->render();
					break;

				case 'save':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_POST)) {

						if (isset($_POST['status'])) {
							$movie = new \models\Request();
							$mapper = ServiceLocator::resolve('requests');

							$movie->status = static::sanitize($_POST['status']);
						} else {
							$movie = new \models\Movie();
							$mapper = ServiceLocator::resolve('movies');
						}

						$movie->id = $movie_id;
						$movie->title = static::sanitize($_POST['title']);
						$movie->year = static::sanitize($_POST['year']);
						$movie->duration = static::sanitize($_POST['duration']);
						$movie->summary = static::sanitize($_POST['summary']);
						$movie->director = static::sanitize($_POST['director']);
						$movie->writer = static::sanitize($_POST['writer']);

						$movie = $mapper->save($movie);

						// Gestisce il caricamento di poster (locandine) o backdrop (sfondi)
						if (($_FILES['poster']['size'] !== 0)
									&& ($_FILES['poster']['type'] === $mapper::MEDIA_TYPE)) {

							$ext = $mapper::MEDIA_EXT;
							$dir = $mapper::POSTERS_PATH;
							$name = $dir.$movie->id.$ext;

							move_uploaded_file($_FILES['poster']['tmp_name'], $name);
						}
						if (($_FILES['backdrop']['size'] !== 0)
									&& ($_FILES['backdrop']['type'] === $mapper::MEDIA_TYPE)) {

							$ext = $mapper::MEDIA_EXT;
							$dir = $mapper::BACKDROPS_PATH;
							$name = $dir.$movie->id.$ext;

							move_uploaded_file($_FILES['backdrop']['tmp_name'], $name);
						}
					}

					$nextView = \views\Movie::factoryMethod($this->session, $movie);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'accept':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$request_id = $movie_id;

					$requests = ServiceLocator::resolve('requests');
					$movies = ServiceLocator::resolve('movies');

					$request = new \models\Request();
					$request->id = $movie_id;
					$request->title = static::sanitize($_POST['title']);
					$request->year = static::sanitize($_POST['year']);
					$request->duration = static::sanitize($_POST['duration']);
					$request->summary = static::sanitize($_POST['summary']);
					$request->director = static::sanitize($_POST['director']);
					$request->writer = static::sanitize($_POST['writer']);
					$movie = \models\Movie::createMovieFromRequest($request);

					$request->status = 'accepted';
					$request = $requests->save($request);
					$movie = $movies->save($movie);

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

					$nextView = \views\Movie::factoryMethod($this->session, $movie);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'reject':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$request_id = $movie_id;

					$requests = ServiceLocator::resolve('requests');
					$request = $requests->getRequestById($request_id);

					$request->status = 'rejected';
					$requests->save($request);

					$nextView = \views\Movie::factoryMethod($this->session, $request);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'delete':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$request_id = $movie_id;

					$requests = ServiceLocator::resolve('requests');
					$request = $requests->getRequestById($request_id);

					$request->status = 'deleted';
					$requests->save($request);

					header('Location: movies.php?action=list_requests');
					break;
			}
		}
	}

	new MovieController();
?>