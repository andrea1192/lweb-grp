<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/MovieView.php');

	class MovieController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';
			$movie = $_GET['id'] ?? 'm1';
			$tab = $_GET['tab'] ?? 'question';

			switch ($action) {

				default:
				case 'display':
					$view = new \views\MovieView($this->session, $movie, $tab);
					$view->render();
					break;

				case 'edit':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\MovieEditView($this->session, $movie);
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
						$movie->id = $_POST['id'];
						$movie->status = $_POST['status'];
						$movie->title = $_POST['title'];
						$movie->year = $_POST['year'];
						$movie->duration = $_POST['duration'];
						$movie->summary = $_POST['summary'];
						$movie->director = $_POST['director'];
						$movie->writer = $_POST['writer'];

						if (empty($movie->status))
							$movie->status = 'submitted';

						$mapper = ServiceLocator::resolve('requests');
						$movie = $mapper->save($movie);
					}

					$nextView = \views\Movie::factoryMethod($this->session, $movie);
					header("Location: {$nextView->generateURL()}");
					break;

				case 'delete':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_GET['id'])) {
						$movie = $_GET['id'];

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