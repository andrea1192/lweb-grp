<?php namespace controllers;

	require_once('session.php');
	require_once('services.php');

	/* Classe base per un controller, inteso come oggetto deputato alla gestione della richiesta
	* dell'utente e all'istanziazione della vista (view) corretta
	*/
	abstract class AbstractController {
		protected $session;

		protected static function checkPOST() {
			if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')
				static::abort('Something went wrong: missing data');
		}

		/* Sanitizza l'input sostituendo caratteri con le rispettive entità ove necessario */
		protected static function sanitize($input) {
			return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}

		/* Annulla l'operazione corrente e reindirizza l'utente alla pagina precedente.
		*
		* Opzionalmente memorizza nella sessione corrente un messaggio d'errore ($message) e/o una
		* lista di campi che presentano dati non validi ($errors), in modo che questi possano
		* essere presentati all'utente dalla vista che verrà invocata.
		*/
		protected static function abort($message = null, $errors = null) {
			$session = ServiceLocator::resolve('session');

			if ($message)
				$session->pushNotification($message);
			if ($errors)
				$session->pushErrors($errors);

			// Rimanda alla pagina precedente o, se non disponibile, alla home page
			$redir = $_SERVER['HTTP_REFERER'] ?? 'index.php';

			header("Location: $redir");
			die();
		}

		public function __construct() {

			spl_autoload_register(function ($class) {
				require('autoloader.php');
			});

			ServiceLocator::register('session', function() {
				return new Session();
			});
			ServiceLocator::register('movies', function() {
				return new \models\Movies();
			});
			ServiceLocator::register('requests', function() {
				return new \models\Requests();
			});
			ServiceLocator::register('posts', function() {
				return new \models\Posts();
			});
			ServiceLocator::register('comments', function() {
				return new \models\Comments();
			});
			ServiceLocator::register('reactions', function() {
				return new \models\Reactions();
			});
			ServiceLocator::register('answers', function() {
				return new \models\Answers();
			});
			ServiceLocator::register('reports', function() {
				return new \models\Reports();
			});
			ServiceLocator::register('users', function() {
				return new \models\Users();
			});

			// Risolve una sessione in modo che venga chiamato session_start() (prima di output)
			$this->session = ServiceLocator::resolve('session');

			if (!str_ends_with($_SERVER['SCRIPT_NAME'], '/install.php')) {
				$this->checkDatabase();
				$this->checkRepository();
			}

			// Determina l'azione da intraprendere (definito da sottoclassi di AbstractController)
			$this->route();
		}

		/* Verifica la disponibilità del database */
		private function checkDatabase() {

			try {
				ServiceLocator::resolve('users');

			} catch (\mysqli_sql_exception $e) {
				$message = "Couldn't connect to the database. Please check your credentials.";

				$this->session->pushNotification($message);
				header('Location: install.php');
				die();
			}
		}

		/* Verifica la disponibilità del repository di file XML */
		private function checkRepository() {
			require_once('connection.php');

			try {
				if (!is_dir(DIR_STATIC))
					throw new \Exception('Couldn\'t load the content archive.');

				ServiceLocator::resolve('movies');
				ServiceLocator::resolve('requests');
				ServiceLocator::resolve('posts');
				ServiceLocator::resolve('comments');
				ServiceLocator::resolve('reactions');
				ServiceLocator::resolve('answers');
				ServiceLocator::resolve('reports');

			} catch (\Exception $e) {
				$message = $e->getMessage();
				$message .= ' Please try restoring and repeating the installation.';

				$this->session->pushNotification($message);
				header('Location: install.php');
				die();
			}
		}
	}
?>