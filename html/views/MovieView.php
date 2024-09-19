<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');

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

		public function __construct($session, $movie_id, $tab) {
			parent::__construct($session);
			$tabs = static::TABS;

			if (preg_match('/m[0-9]*/', $movie_id)) {
				$this->tabs = $tabs['movie'];
				$this->tab = $tab;
				$this->movie = $this->getMapper('movies')->getMovieById($movie_id);
				$this->posts = $this->getMapper('posts')->getPostsByMovie($movie_id, $tab);

			} else {
				$this->tabs = $tabs['request'];
				$this->tab = 'comment';
				$this->movie = $this->getMapper('requests')->getRequestById($movie_id);
				$this->posts = $this->getMapper('comments')->getCommentsByRequest($movie_id);
			}

			if ($this->tab == 'question')
				$this->featuredPosts = $this->getMapper('posts')->getFeaturedPosts($movie_id);
		}

		public function printTitle() {
			print("{$this->movie->title} ({$this->movie->year}) - {$this->tabs[$this->tab]} - grp");

		}

		public function printOverview() {
			$view = \views\Movie::factoryMethod($this->session, $this->movie);
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

			if (!$this->posts->count()) {
				$icon = UIComponents::getIcon('search', 'no_results');

				echo <<<EOF
				<div class="flex align">
					{$icon}
					<span>No posts of type "{$this->tab}" found.</span>
				</div>
				EOF;
				return;
			}

			if ($this->tab == 'question') {
				print("<h1>Featured posts</h1>");

				foreach ($this->featuredPosts as $featuredPost) {
					$view = \views\Post::factoryMethod($this->session, $featuredPost);
					$view->displayFeatured();
				}

				print("<h1>All posts</h1>");
			}

			foreach ($this->posts as $post) {
				$view = \views\Post::factoryMethod($this->session, $post);
				$view->display();
			}
		}

		public function printActionButton() {
			$URL = "post.php?action=create&type={$this->tab}&movie={$this->movie->id}";
			$URL = htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);

			if ($this->session->isAllowed())
				print(UIComponents::getFAB('New post', 'add', $URL));
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}

	abstract class AbstractEditView extends AbstractView {
		public $movie;

		public function editOverview() {
			$view = \views\Movie::factoryMethod($this->session, $this->movie);
			$view->edit();
		}

		public function render() {
			require_once('templates/MovieEditTemplate.php');
		}
	}

	class MovieEditView extends AbstractEditView {

		public function __construct($session, $movie_id) {
			parent::__construct($session);

			if (preg_match('/m[0-9]*/', $movie_id))
				$this->movie = $this->getMapper('movies')->getMovieById($movie_id);
			else
				$this->movie = $this->getMapper('requests')->getRequestById($movie_id);
		}

		public function printTitle() {
			print("Editing {$this->movie->title} ({$this->movie->year}) - grp");

		}
	}

	class MovieCreateView extends AbstractEditView {

		public function __construct($session) {
			parent::__construct($session);

			$this->movie = new \models\Request();
		}

		public function printTitle() {
			print("New movie - grp");

		}
	}
?>