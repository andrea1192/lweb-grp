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

							if (empty($movie->status))
								$movie->status = 'submitted';
							else
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
					$requests->save($request);
					$movies->save($movie);

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
					$mapper = ServiceLocator::resolve('requests');
					$mapper->delete($movie_id);

					header('Location: movies.php?action=list_requests');
					break;
			}
		}
	}

	new MovieController();
?>