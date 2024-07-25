<?php namespace controllers;

	class MockSession {
		private $username = 'bar';
		private $privilege = 2;

		public function getUsername() {
			return $this->username;
		}

		public function isLoggedIn() {
			return isset($this->username);
		}

		public function isRegistered() {
			return (bool) ($this->privilege >= 1);
		}

		public function isMod() {
			return (bool) ($this->privilege >= 2);
		}

		public function isAdmin() {
			return (bool) ($this->privilege == 3);
		}

		public function isAuthor($object) {
			return (bool) ($this->username == $object->author);
		}
	}