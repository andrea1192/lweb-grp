<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');

	class PostView extends AbstractView {

		public $post;
		public $movie;

		public function __construct($session, $post_id) {
			parent::__construct($session);

			$this->post = \models\Posts::getPostById($post_id);
			$this->movie = \models\Movies::getMovieById($this->post->movie);
		}

		public function printTitle() {
			print("Post: {$this->post->title} - grp");

		}

		public function printMovieReference() {
			$view = \views\Movie::factoryMethod($this->session, $this->movie);
			$view->displayReference();
		}

		public function printPost() {
			$view = \views\Post::factoryMethod($this->session, $this->post);
			$view->display();
		}

		public function render() {
			require_once('templates/PostDisplayTemplate.php');
		}
	}
?>