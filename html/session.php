<?php namespace controllers;

	require_once('models/Users.php');

	class Session {
		private $user;

		public function __construct() {
			session_start();

			if (isset($_SESSION['username']))
				$this->setUser($_SESSION['username']);
		}

		public function getUser() {
			return $this->user;
		}

		public function setUser($username) {
			if ($username) {
				$this->user = ServiceLocator::resolve('users')->getUserByUsername($username);

				$_SESSION['username'] = $username;
			} else {

				unset($_SESSION['username']);
			}
		}

		public function getUsername() {
			return ($this->isLoggedIn()) ? $this->user->username : 'Visitor';
		}

		public function getReputation() {
			return ($this->isLoggedIn()) ? $this->user->reputation : 0;
		}

		public function getUserType() {
			return ($this->isLoggedIn()) ? $this->user->getUserType() : 'Guest';
		}

		public function isLoggedIn() {
			return isset($_SESSION['username']);
		}

		public function isAllowed() {
			return (($this->isLoggedIn()) && ($this->user->isAllowed()));
		}

		public function isMod() {
			return (($this->isLoggedIn()) && ($this->user->isMod()));
		}

		public function isAdmin() {
			return (($this->isLoggedIn()) && ($this->user->isAdmin()));
		}

		public function isAuthor($object) {
			return (($this->isLoggedIn()) && ($this->user->isAuthor($object)));
		}

		public function holdsNotification() {
			return isset($_SESSION['notification']);
		}

		public function pushNotification($message) {
			$_SESSION['notification'] = $message;
		}

		public function popNotification() {
			$notification = $_SESSION['notification'];
			unset($_SESSION['notification']);

			return $notification;
		}
	}
?>