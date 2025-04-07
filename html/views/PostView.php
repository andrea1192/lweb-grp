<?php namespace views;

	/* Classe base per la composizione, visualizzazione o modifica di un post */
	abstract class AbstractPostView extends AbstractView {
		public $post;
		public $movie;

		/* Visualizza alcuni dettagli della scheda di riferimento */
		public function printMovieReference() {
			$view = \views\Movie::matchModel($this->movie);
			$view->displayReference();
		}
	}

	/* Visualizzazione dettagliata di un post */
	class PostView extends AbstractPostView {

		public function __construct($post_id) {
			parent::__construct();

			$post_type = \models\Post::getType($post_id);

			switch ($post_type) {
				default:
					$this->post =
							$this->getMapper($post_type.'s')->getPostById($post_id)
					and $this->movie =
							$this->getMapper('movies')->getMovieById($this->post->movie);
					break;
				case 'comment':
					$this->post =
							$this->getMapper('comments')->getCommentById($post_id)
					and $this->movie =
							$this->getMapper('requests')->getRequestById($this->post->movie);
					break;
			}

			if (empty($this->post)) {
				$this->session->pushNotification(
						"Post #{$post_id} not found in the archive. Sorry about that.");
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

	/* Form di modifica di un post */
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

	/* Form di composizione di un nuovo post */
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

	/* Form di composizione di una reazione estesa (es. risposta a domanda o segnalazione di post).
	* A differenza delle altre specializzazioni di PostView, riferimento è a post invece di scheda.
	*/
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