<?php namespace controllers;

	require_once('session.php');
	require_once('services.php');

	abstract class AbstractController {
		protected $session;

		protected static function sanitize($input) {
			return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}

		protected static function abort($message = null, $errors = null) {
			$session = ServiceLocator::resolve('session');

			if ($message)
				$session->pushNotification($message);
			if ($errors)
				$session->pushErrors($errors);

			header("Location: {$_SERVER['HTTP_REFERER']}");
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

			$this->session = ServiceLocator::resolve('session');

			if ($_SERVER['SCRIPT_NAME'] != '/install.php')
				$this->checkDatabase();

			$this->route();
		}

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
	}
?>