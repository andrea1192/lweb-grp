<?php namespace views;

	/* Classe base per visualizzare collezioni di oggetti */
	abstract class AbstractCollectionView extends AbstractView {
		public $title;
		public $items;

		public function printTitle() {
			print("{$this->title} - grp");
		}

		/* Stampa un messaggio che informa l'utente quando la collezione di riferimento è vuota */
		public static function printEmptyMessage() {
			$icon = UIComponents::getIcon('sentiment_dissatisfied', cls: 'md-72 md-xlight');

			print <<<EOF
			<div class="flex column cross-center colored-grey">
				$icon
				<span>Nothing to show</span>
			</div>
			EOF;
		}

		/* Stampa il bottone con l'azione principale (nessuna, di default) */
		public function printActionButton() {}
	}

	/* Visualizzazione di tipo lista (sequenza verticale con normal flow) */
	abstract class AbstractListView extends AbstractCollectionView {

		public function printList() {
			print("<h1>{$this->title}</h1>");

			if (!count($this->items)) {
				static::printEmptyMessage();
				return;
			}

			print('<div>');

			foreach ($this->items as $item) {
				$view = \views\AbstractView::matchModel($item);
				$view->display();
			}

			print('</div>');
		}

		public function render() {
			require_once('templates/ListTemplate.php');
		}
	}

	/* Visualizzazione di tipo griglia (sequenza orizzontale con flex-wrap) */
	abstract class AbstractGridView extends AbstractCollectionView {

		public function printGrid() {
			print("<h1>{$this->title}</h1>");

			if (!count($this->items)) {
				static::printEmptyMessage();
				return;
			}

			print('<div class="flex grid">');

			foreach ($this->items as $item) {
				$view = \views\AbstractView::matchModel($item);
				$view->displayCard();
			}

			print('</div>');
		}

		public function render() {
			require_once('templates/GridTemplate.php');
		}
	}

	/* Griglia delle schede/richieste in archivio */
	class MoviesView extends AbstractGridView {

		public function __construct($item_type) {
			parent::__construct();

			switch ($item_type) {
				case 'movies':
					$this->title = 'Movies';
					$this->items = $this->getMapper('movies')->getMovies();
					break;
				case 'requests':
					$this->title = 'Requests';
					$this->items = $this->getMapper('requests')->getRequests();
					break;
			}
		}

		/* Stampa il bottone con l'azione principale (composizione di una nuova richiesta) */
		public function printActionButton() {

			if ($this->session->isAllowed())
				print(UIComponents::getFAB('Add movie', 'add', 'movie.php?action=compose'));
		}
	}

	/* Lista dei report inviati. Cambia in base al privilegio dell'utente corrente: moderatori ed
	* amministratori vedono tutti i report, mentre gli utenti regolari solo quelli inviati da loro.
	*/
	class ReportsView extends AbstractListView {

		public function __construct() {
			parent::__construct();

			$reports = $this->getMapper('reports');
			$current_user = $this->session->getUsername();

			if ($this->session->isMod()) {
				$this->title = 'User Reports';
				$this->items = $reports->getReports();
			} else {
				$this->title = 'Your Reports';
				$this->items = $reports->getReportsByAuthor($current_user);
			}
		}

		public function printList() {
			print("<h1>{$this->title}</h1>");

			if (!count($this->items)) {
				static::printEmptyMessage();
				return;
			}

			print('<div>');

			foreach ($this->items as $item) {
				$type = \models\AbstractModel::getType($item->post);
				$repo = $this->getMapper($type.'s');
				$post = $repo->getPostById($item->post);

				$postView = \views\AbstractView::matchModel($post);
				$reactionView = \views\AbstractView::matchModel($item);

				$reaction = $reactionView->generate();
				$postView->displayReference(active: false, reactions: $reaction);
			}

			print('</div>');
		}
	}

	/* Lista degli utenti registrati */
	class UsersView extends AbstractListView {

		public function __construct() {
			parent::__construct();

			$users = $this->getMapper('users');

			$this->title = 'Users';
			$this->items = $users->readAll();
		}

		public function printList() {
			print("<h1>{$this->title}</h1>");

			if (!count($this->items)) {
				static::printEmptyMessage();
				return;
			}

			print('<div>');

			foreach ($this->items as $user) {
				$view = new User($user);
				$view->displayListItem();
			}

			print('</div>');
		}
	}
?>