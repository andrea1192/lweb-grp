<?php namespace views;

	class MovieView extends AbstractView {
		public const TABS = [
			'movie' => [
				'review' => 'Reviews',
				'question' => 'Q&amp;A',
				'spoiler' => 'Spoilers',
				'extra' => 'Extras'
			],
			'request' =>  [
				'comment' => 'Comments'
			]];

		public $tabs;
		public $tab;
		public $movie;
		public $posts;
		public $featuredPosts;

		public function __construct($movie_id, $tab) {
			parent::__construct();
			$tabs = static::TABS;

			switch (\models\Movie::getType($movie_id)) {
				case 'movie':
					$this->tabs = $tabs['movie'];
					$this->tab = (!empty($tab)) ? $tab : 'question';
					$this->movie =
							$this->getMapper('movies')->getMovieById($movie_id);
					$this->posts =
							$this->getMapper('posts')->getPostsByMovie($movie_id, $this->tab);
					break;
				case 'request':
					$this->tabs = $tabs['request'];
					$this->tab = (!empty($tab)) ? $tab : 'comment';
					$this->movie =
							$this->getMapper('requests')->getRequestById($movie_id);
					$this->posts =
							$this->getMapper('comments')->getCommentsByRequest($movie_id);
					break;
			}

			if (empty($this->movie)) {
				$this->session->pushNotification(
						"Movie #{$movie_id} not found in the archive. Sorry about that.");
				header('Location: index.php');
				die();
			}

			if ($this->tab == 'question')
				$this->featuredPosts = $this->getMapper('posts')->getFeaturedPosts($movie_id);
		}

		public function printTitle() {
			print("{$this->movie->title} ({$this->movie->year}) - {$this->tabs[$this->tab]} - grp");

		}

		public function printOverview() {
			$view = \views\Movie::matchModel($this->movie);
			$view->display();
		}

		private function printTabs() {
			$base_URL = $_SERVER['SCRIPT_NAME'];

			foreach ($this->tabs as $tab => $label) {
				$query = [
					'id' => $this->movie->id,
					'type' => $tab
				];

				$URL = $base_URL.'?'.http_build_query($query);
				$URL = htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);

				$active = ($this->tab == $tab) ? 'class="active"' : '';

				print("<li><a href=\"{$URL}\" {$active}>{$label}</a></li>\n");
			}
		}

		public function printPosts() {
			$components = 'views\UIComponents';

			if (!$this->posts->count()) {

				echo <<<EOF
				<div class="flex cross-center">
					{$components::getIcon('search', 'md-48 margin')}
					<span>No posts of type "{$this->tab}" found.</span>
				</div>
				EOF;
				return;
			}

			if ($this->tab == 'question' && $this->featuredPosts->count()) {

				echo <<<EOF
				<div class="featured">
					<div class="flex cross-center">
						{$components::getIcon('verified', 'md-24 margin')}
						<h1>Featured posts</h1>
					</div>
				EOF;

				foreach ($this->featuredPosts as $featuredPost) {
					$view = \views\Post::matchModel($featuredPost, $this->movie);
					$view->displayFeatured();
				}

				echo '</div>';
			}

			foreach ($this->posts as $post) {
				$view = \views\Post::matchModel($post, $this->movie);
				$view->display();
			}
		}

		public function printActionButton() {

			if (property_exists($this->movie, 'status') && ($this->movie->status != 'submitted'))
				return '';

			$URL = "post.php?action=compose&type={$this->tab}&movie={$this->movie->id}";
			$URL = htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);

			if ($this->session->isAllowed()
					&& ($this->tab != 'extra' || $this->session->isMod()))
				print(UIComponents::getFAB('New post', 'add', $URL));
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}

	abstract class AbstractEditView extends AbstractView {
		public $movie;

		public function render() {
			require_once('templates/MovieEditTemplate.php');
		}
	}

	class MovieEditView extends AbstractEditView {

		public function __construct($movie_id) {
			parent::__construct();

			switch (\models\Movie::getType($movie_id)) {
				case 'movie':
					$this->movie = $this->getMapper('movies')->getMovieById($movie_id);
					break;
				case 'request':
					$this->movie = $this->getMapper('requests')->getRequestById($movie_id);
					break;
			}
		}

		public function printForm() {
			$view = \views\Movie::matchModel($this->movie);
			$view->edit();
		}

		public function printTitle() {
			print("Editing {$this->movie->title} ({$this->movie->year}) - grp");

		}
	}

	class MovieComposeView extends AbstractEditView {

		public function __construct() {
			parent::__construct();

			$this->movie = null;
		}

		public function printForm() {
			$view = new \views\Request($this->movie);
			$view->compose();
		}

		public function printTitle() {
			print("New movie - grp");

		}
	}
?>