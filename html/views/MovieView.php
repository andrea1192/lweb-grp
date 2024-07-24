<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');

	class MovieView extends AbstractView{
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

			$tabs = [
				'review' => 'Reviews',
				'question' => 'Q&amp;A',
				'spoiler' => 'Spoilers',
				'extra' => 'Extras'
			];

			print("{$this->movie->title} ({$this->movie->year}) - {$tabs[$this->tab]} - grp");

		}

		public function printOverview() {
			print(\views\Movie::generateHTML($this->movie, $this->tab));
		}

		public function printPosts() {

			foreach ($this->posts as $post) {
				print(\views\Post::generateHTML($post));
			}
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}
?>