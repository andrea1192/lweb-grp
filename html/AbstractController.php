<?php namespace controllers;

	require_once('session.php');
	require_once('services.php');

	abstract class AbstractController {
		protected $session;

		protected static function sanitize($input) {
			return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}

		public function __construct() {

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

			$this->loadSession();
			$this->route();
		}

		protected function loadSession() {

			$this->session = ServiceLocator::resolve('session');
		}
	}
?>