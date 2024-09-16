<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MovieView.php');

	class MovieController extends AbstractController {

		public function route() {
			$movie_id = static::sanitize($_GET['id'] ?? '');
			$tab = static::sanitize($_GET['tab'] ?? 'question');

			switch ($_GET['action'] ?? '') {

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
						$movie = new \models\Request();
						$movie->id = static::sanitize($_POST['id']);
						$movie->status = static::sanitize($_POST['status']);
						$movie->title = static::sanitize($_POST['title']);
						$movie->year = static::sanitize($_POST['year']);
						$movie->duration = static::sanitize($_POST['duration']);
						$movie->summary = static::sanitize($_POST['summary']);
						$movie->director = static::sanitize($_POST['director']);
						$movie->writer = static::sanitize($_POST['writer']);

						if (empty($movie->status))
							$movie->status = 'submitted';

						$mapper = ServiceLocator::resolve('requests');
						$movie = $mapper->save($movie);
					}

					$nextView = \views\Movie::factoryMethod($this->session, $movie);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'accept':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['id'])) {
						$request_id = static::sanitize($_GET['id']);

						$requests = ServiceLocator::resolve('requests');
						$movies = ServiceLocator::resolve('movies');

						$request = $requests->getRequestById($request_id);
						$movie = \models\Movie::createMovieFromRequest($request);

						$request->status = 'accepted';
						$requests->save($request);
						$movies->save($movie);
					}

					$nextView = \views\Movie::factoryMethod($this->session, $movie);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'reject':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['id'])) {
						$request_id = static::sanitize($_GET['id']);

						$requests = ServiceLocator::resolve('requests');
						$request = $requests->getRequestById($request_id);

						$request->status = 'rejected';
						$requests->save($request);
					}

					$nextView = \views\Movie::factoryMethod($this->session, $request);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'delete':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['id'])) {
						$movie = static::sanitize($_GET['id']);

						$mapper = ServiceLocator::resolve('requests');
						$mapper->delete($movie);
					}

					header('Location: movies.php?action=list_requests');
					break;
			}
		}
	}

	new MovieController();
?>