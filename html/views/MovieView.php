<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');

	class MovieView {
		public $movie;

		public function __construct($movie_id) {

			$this->movie = \models\Movies::getMovie($movie_id);
		}

		public function printOverview() {
			print(\views\Movie::generateHTML($this->movie));
		}

		public function printPosts($filter) {

			foreach ($this->movie->posts as $post) {

				print(\views\Post::generateHTML($post));
			}
		}
	}
?>