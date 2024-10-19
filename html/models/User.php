<?php namespace models;

	class User {
		public $username;
		public $password;
		public $name;
		public $address;
		public $mail_pri;
		public $mail_sec;
		public $reputation;

		private $privilege;

		public function __construct($state = null) {

			if (!$state)
				return;

			foreach ($state as $key => $value) {
				if (property_exists($this, $key)) {
					$this->$key = $value;
				}
			}
		}

		public function getState() {
			return array_filter(
					get_object_vars($this),
					fn($property) => isset($property));
		}

		public function getUserType() {

			switch ($this->privilege) {
				case -1: return 'Disabled account';
				case 0: return 'Banned';
				default:
				case 1: return 'User';
				case 2: return 'Moderator';
				case 3: return 'Administrator';
			}
		}

		public function setPrivilege($level) {
			if ($level >= -1 && $level <= 3)
				$this->privilege = $level;
		}

		public function isEnabled() {
			return $this->privilege >= 0;
		}

		public function isAllowed() {
			return $this->privilege >= 1;
		}

		public function isMod() {
			return $this->privilege >= 2;
		}

		public function isAdmin() {
			return $this->privilege == 3;
		}

		public function isAuthor($object) {
			return $this->username == $object->author;
		}

	}
?>