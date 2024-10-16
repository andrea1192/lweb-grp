<?php namespace controllers;

	require_once('models/Users.php');

	class MockSession {
		private $user;

		public function __construct() {
			$this->user = ServiceLocator::resolve('users')->getUserByUsername('bar');
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
			return isset($this->user);
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
	}
?>