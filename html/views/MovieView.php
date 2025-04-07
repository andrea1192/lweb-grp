<?php namespace views;

	/* Visualizzazione della scheda di un film o di una richiesta, con una parte dei relativi post
	* a seconda della sottopagina selezionata
	*/
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

		public $type;
		public $tab;
		public $movie;
		public $posts;
		public $featuredPosts;

		public function __construct($movie_id, $movie_type, $tab) {
			parent::__construct();
			$this->type = $movie_type;

			if ($this->type == 'movie') {
				$this->tab = (!empty($tab)) ? $tab : 'question';
				$this->movie =
						$this->getMapper('movies')->getMovieById($movie_id);
				$this->posts =
						$this->getMapper($this->tab.'s')->getPostsByMovie($movie_id);

			} else {
				$this->tab = (!empty($tab)) ? $tab : 'comment';
				$this->movie =
						$this->getMapper('requests')->getRequestById($movie_id);
				$this->posts =
						$this->getMapper('comments')->getCommentsByRequest($movie_id);
			}

			if (empty($this->movie)) {
				$this->session->pushNotification(
						"Movie #{$movie_id} not found in the archive. Sorry about that.");
				header('Location: index.php');
				die();
			}

			if ($this->tab == 'question')
				$this->featuredPosts = $this->getMapper('questions')->getFeaturedQuestions($movie_id);
		}

		public function printTitle() {
			$tabs = static::TABS[$this->type];
			print("{$this->movie->title} ({$this->movie->year}) - {$tabs[$this->tab]} - grp");
		}

		/* Stampa i dettagli principali, delegando ad un opportuno oggetto Movie/Request */
		public function printOverview() {
			$view = \views\Movie::matchModel($this->movie);
			$view->display();
		}

		/* Stampa le sottopagine disponibili, con le diverse tipologie di post */
		private function printTabs() {
			$base_URL = $_SERVER['SCRIPT_NAME'];

			foreach (static::TABS[$this->type] as $tab => $label) {
				$query = [
					'type' => $this->type,
					'id' => $this->movie->id,
					'tab' => $tab
				];

				$URL = $base_URL.'?'.http_build_query($query);
				$URL = htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);

				$active = ($this->tab == $tab) ? 'class="active"' : '';

				print("<li><a href=\"{$URL}\" {$active}>{$label}</a></li>\n");
			}
		}

		/* Stampa i post */
		public function printPosts() {
			$components = 'views\UIComponents';

			if (empty($this->posts)) {

				echo <<<EOF
				<div class="flex cross-center">
					{$components::getIcon('search', 'md-48 margin')}
					<span>No posts of type "{$this->tab}" found.</span>
				</div>
				EOF;
				return;
			}

			if ($this->tab == 'question' && (!empty($this->posts))) {

				echo <<<EOF
				<div class="featured">
					<div class="flex cross-center">
						{$components::getIcon('verified', 'md-24 margin')}
						<h1>Featured posts</h1>
					</div>
				EOF;

				foreach ($this->featuredPosts as $featuredPost) {
					$view = \views\Post::matchModel($featuredPost, $this->movie);
					$view->displayFeatured();
				}

				echo '</div>';
			}

			foreach ($this->posts as $post) {
				$view = \views\Post::matchModel($post, $this->movie);
				$view->display();
			}
		}

		/* Stampa il bottone con l'azione principale (composizione di un post di questo tipo) */
		public function printActionButton() {

			if (property_exists($this->movie, 'status') && ($this->movie->status != 'submitted'))
				return '';

			$URL = "post.php?action=compose&type={$this->tab}&movie={$this->movie->id}";
			$URL = htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);

			if ($this->session->isAllowed()
					&& ($this->tab != 'extra' || $this->session->isMod()))
				print(UIComponents::getFAB('New post', 'add', $URL));
		}

		public function render() {
			require_once('templates/MovieDisplayTemplate.php');
		}
	}

	abstract class AbstractEditView extends AbstractView {
		public $movie;

		public function render() {
			require_once('templates/MovieEditTemplate.php');
		}
	}

	/* Form di modifica della scheda di un film o di una richiesta */
	class MovieEditView extends AbstractEditView {

		public function __construct($movie_id, $movie_type) {
			parent::__construct();

			if ($movie_type == 'movie') {
				$this->movie =
						$this->getMapper('movies')->getMovieById($movie_id);

			} else {
				$this->movie =
						$this->getMapper('requests')->getRequestById($movie_id);
			}
		}

		public function printForm() {
			$view = \views\Movie::matchModel($this->movie);
			$view->edit();
		}

		public function printTitle() {
			print("Editing {$this->movie->title} ({$this->movie->year}) - grp");

		}
	}

	/* Form di composizione di una nuova richiesta */
	class MovieComposeView extends AbstractEditView {

		public function __construct() {
			parent::__construct();

			$this->movie = null;
		}

		public function printForm() {
			$view = new \views\Request($this->movie);
			$view->compose();
		}

		public function printTitle() {
			print("New movie - grp");

		}
	}
?>