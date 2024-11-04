<?php namespace views;

	require_once('models/Movies.php');
	require_once('views/AbstractView.php');
	require_once('views/Movie.php');
	require_once('views/Post.php');
	require_once('views/Reaction.php');

	abstract class AbstractPostView extends AbstractView {
		public $post;
		public $movie;

		public function printMovieReference() {
			$view = \views\Movie::factoryMethod($this->session, $this->movie);
			$view->displayReference();
		}
	}

	class PostView extends AbstractPostView {

		public function __construct($session, $post_id) {
			parent::__construct($session);

			switch (\models\Post::getType($post_id)) {
				default:
					$this->post = $this->getMapper('posts')->getPostById($post_id);
					$this->movie = $this->getMapper('movies')->getMovieById($this->post->movie);
					break;
				case 'comment':
					$this->post = $this->getMapper('comments')->getCommentById($post_id);
					$this->movie = $this->getMapper('requests')->getRequestById($this->post->request);
					break;
			}
		}

		public function printPost() {
			$view = \views\Post::factoryMethod($this->session, $this->post);
			$view->display();
		}

		public function printTitle() {
			print("Post: {$this->post->title} - grp");

		}

		public function render() {
			require_once('templates/PostDisplayTemplate.php');
		}
	}

	class PostEditView extends PostView {

		public function printForm() {
			$view = \views\Post::factoryMethod($this->session, $this->post);
			$view->edit();
		}

		public function printTitle() {
			print("Editing post: {$this->post->title} - grp");

		}

		public function render() {
			require_once('templates/PostEditTemplate.php');
		}
	}

	class PostComposeView extends AbstractPostView {
		public $post_type;

		public function __construct($session, $post_type, $movie_id) {
			parent::__construct($session);

			$this->post_type = $post_type;

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
			$view = \views\Post::build($this->session, $this->post_type, null, $this->movie->id);
			$view->compose();
		}

		public function printTitle() {
			print("New post - grp");

		}

		public function render() {
			require_once('templates/PostEditTemplate.php');
		}
	}

	class ReactionCreateView extends PostView {
		public $reaction_type;

		public function __construct($session, $reaction_type, $post_id) {
			parent::__construct($session, $post_id);

			$this->reaction_type = $reaction_type;
		}

		public function printForm() {
			$postView = \views\AbstractView::factoryMethod($this->session, $this->post);
			$reactionView = \views\Reaction::build($this->session, $this->reaction_type, null, $this->post);

			$reaction = $reactionView->generateInsertForm();
			$postView->displayReference(active: false, reactions: $reaction);
		}

		public function render() {
			require_once('templates/PostEditTemplate.php');
		}
	}
?>