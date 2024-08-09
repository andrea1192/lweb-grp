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

		public function __construct($session, $movie_id, $tab = 'question') {
			parent::__construct($session);
			$tabs = static::TABS;

			if (preg_match('/m[0-9]*/', $movie_id)) {
				$this->tabs = $tabs['movie'];
				$this->tab = $tab;
				$this->movie = \models\Movies::getMovieById($movie_id);
				$this->posts = \models\Posts::getPostsByMovie($movie_id, $tab);

			} else {
				$this->tabs = $tabs['request'];
				$this->tab = 'comment';
				$this->movie = \models\Requests::getRequestById($movie_id);
				$this->posts = \models\Comments::getCommentsByRequest($movie_id);
			}
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
					'tab' => $tab
				];

				$URL = $base_URL.'?'.http_build_query($query);

				$active = ($this->tab == $tab) ? 'class="active"' : '';

				print("<li><a href=\"{$URL}\" {$active}>{$label}</a></li>");
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

			foreach ($this->posts as $post) {
				$view = \views\Post::factoryMethod($this->session, $post);
				$view->display();
			}
		}

		public function printActionButton() {

			if ($this->session->isAllowed())
				print(UIComponents::getFAB('New post', 'add', '#'));
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}

	class MovieEditView extends MovieView {

		public function printTitle() {
			print("Editing {$this->movie->title} ({$this->movie->year}) - grp");

		}

		public function editOverview() {
			$view = \views\Movie::factoryMethod($this->session, $this->movie);
			$view->edit();
		}

		public function render() {
			require_once('templates/MovieEditTemplate.php');
		}
	}
?>