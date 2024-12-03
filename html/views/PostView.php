<?php namespace views;

	abstract class AbstractPostView extends AbstractView {
		public $post;
		public $movie;

		public function printMovieReference() {
			$view = \views\Movie::matchModel($this->movie);
			$view->displayReference();
		}
	}

	class PostView extends AbstractPostView {

		public function __construct($post_id) {
			parent::__construct();

			switch (\models\Post::getType($post_id)) {
				default:
					$this->post = $this->getMapper('posts')->getPostById($post_id);

					if ($this->post)
						$this->movie = $this->getMapper('movies')->getMovieById($this->post->movie);
					break;
				case 'comment':
					$this->post = $this->getMapper('comments')->getCommentById($post_id);

					if ($this->post)
						$this->movie = $this->getMapper('requests')->getRequestById($this->post->request);
					break;
			}

			if (empty($this->post)) {
				$this->session->pushNotification("Post #{$post_id} not found in the archive. Sorry about that.");
				header('Location: index.php');
				die();
			}
		}

		public function printPost() {
			$view = \views\Post::matchModel($this->post);
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
			$view = \views\Post::matchModel($this->post);
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

		public function __construct($post_type, $movie_id) {
			parent::__construct();

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
			$view = \views\Post::build($this->post_type, null, $this->movie->id);
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

		public function __construct($reaction_type, $post_id) {
			parent::__construct($post_id);

			$this->reaction_type = $reaction_type;
		}

		public function printForm() {
			$postView = \views\AbstractView::matchModel($this->post);
			$reactionView = \views\Reaction::build($this->reaction_type, null, $this->post);

			$reaction = $reactionView->generateInsertForm();
			$postView->displayReference(active: false, reactions: $reaction);
		}

		public function render() {
			require_once('templates/PostEditTemplate.php');
		}
	}
?>