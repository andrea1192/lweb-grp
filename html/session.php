<?php namespace controllers;

	class MockSession {
		private $username = 'bar';
		private $privilege = 2;
		private $reputation = 42;

		public function getUsername() {
			return ($this->isLoggedIn()) ? $this->username : '';
		}

		public function getReputation() {
			return ($this->isLoggedIn()) ? $this->reputation : 0;
		}

		public function getUserType() {

			switch ($this->privilege) {
				case 0: return 'Banned';
				default:
				case 1: return 'User';
				case 2: return 'Moderator';
				case 3: return 'Administrator';
			}
		}

		public function isLoggedIn() {
			return isset($this->username);
		}

		public function isAllowed() {
			return (bool) (($this->isLoggedIn()) && ($this->privilege >= 1));
		}

		public function isMod() {
			return (bool) (($this->isLoggedIn()) && ($this->privilege >= 2));
		}

		public function isAdmin() {
			return (bool) (($this->isLoggedIn()) && ($this->privilege == 3));
		}

		public function isAuthor($object) {
			return (bool) (($this->isLoggedIn()) && ($this->getUsername() == $object->author));
		}
	}
?>