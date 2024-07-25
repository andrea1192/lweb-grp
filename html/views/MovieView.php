<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');

	class MovieView extends AbstractView {
		public const tabs = [
				'review' => 'Reviews',
				'question' => 'Q&amp;A',
				'spoiler' => 'Spoilers',
				'extra' => 'Extras'
			];

		public $movie;
		public $tab;
		public $posts;

		public function __construct($session, $movie_id, $tab) {
			parent::__construct($session);

			$this->movie = \models\Movies::getMovieById($movie_id);
			$this->tab = $tab;
			$this->posts = \models\Posts::getPostsByMovie($movie_id, $tab);
		}

		public function printTitle() {
			$tabs = self::tabs;

			print("{$this->movie->title} ({$this->movie->year}) - {$tabs[$this->tab]} - grp");

		}

		public function printOverview() {
			$view = new \views\Movie($this->session, $this->movie);
			$view->render();
		}

		private function printTabs() {
			$base_URL = $_SERVER['SCRIPT_NAME'];

			foreach (self::tabs as $tab => $label) {
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

			foreach ($this->posts as $post) {
				$view = new \views\Post($this->session, $post);
				$view->render();
			}
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}
?>