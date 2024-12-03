<?php namespace views;

	abstract class AbstractCollectionView extends AbstractView {
		public $title;
		public $items;

		public function printTitle() {
			print("{$this->title} - grp");
		}

		public function printActionButton() {}
	}

	abstract class AbstractListView extends AbstractCollectionView {

		public function printList() {

			print("<h1>{$this->title}</h1>");
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

	abstract class AbstractGridView extends AbstractCollectionView {

		public function printGrid() {

			print("<h1>{$this->title}</h1>");
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

		public function printActionButton() {

			if ($this->session->isAllowed())
				print(UIComponents::getFAB('Add movie', 'add', 'movie.php?action=compose'));
		}
	}

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
			print('<div>');

			foreach ($this->items as $item) {
				$post = $this->getMapper('posts')->getPostById($item->post);

				$postView = \views\AbstractView::matchModel($post);
				$reactionView = \views\AbstractView::matchModel($item);

				$reaction = $reactionView->generate();
				$postView->displayReference(active: false, reactions: $reaction);
			}

			print('</div>');
		}
	}

	class UsersView extends AbstractListView {

		public function __construct() {
			parent::__construct();

			$users = $this->getMapper('users');

			$this->title = 'Users';
			$this->items = $users->readAll();
		}

		public function printList() {
			print("<h1>{$this->title}</h1>");
			print('<div>');

			foreach ($this->items as $user) {
				$view = new User($user);
				$view->displayListItem();
			}

			print('</div>');
		}
	}
?>