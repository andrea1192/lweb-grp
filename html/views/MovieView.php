<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');

	abstract class AbstractMovieView extends AbstractView {
		public $movie;
		public $posts;

		public function printTitle() {
			$tabs = static::tabs;

			print("{$this->movie->title} ({$this->movie->year}) - {$tabs[$this->tab]} - grp");

		}

		public function printOverview() {
			$view = \views\Movie::factoryMethod($this->session, $this->movie);
			$view->render();
		}

		private function printTabs() {
			$base_URL = $_SERVER['SCRIPT_NAME'];

			foreach (static::tabs as $tab => $label) {
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
				echo <<<EOF
				<div class="flex align notfound">
					<span class="material-symbols-outlined"></span>
					<span>No posts of type "{$this->tab}" found.</span>
				</div>
				EOF;
				return;
			}

			foreach ($this->posts as $post) {
				$view = \views\Post::factoryMethod($this->session, $post);
				$view->render();
			}
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}

	class MovieView extends AbstractMovieView {
		public const tabs = [
				'review' => 'Reviews',
				'question' => 'Q&amp;A',
				'spoiler' => 'Spoilers',
				'extra' => 'Extras'
			];

		public $tab;

		public function __construct($session, $movie_id, $tab) {
			parent::__construct($session);

			$this->movie = \models\Movies::getMovieById($movie_id);
			$this->posts = \models\Posts::getPostsByMovie($movie_id, $tab);
			$this->tab = $tab;
		}
	}

	class RequestView extends AbstractMovieView {
		public const tabs = ['comment' => 'Comments'];

		public $tab = 'comment';

		public function __construct($session, $movie_id) {
			parent::__construct($session);

			$this->movie = \models\Requests::getRequestById($movie_id);
			$this->posts = \models\Comments::getCommentsByRequest($movie_id);
		}
	}
?>