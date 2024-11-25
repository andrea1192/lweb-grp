<?php namespace models;

	require_once('models/AbstractModel.php');

	class User extends AbstractModel {
		public const REPUTATION_DELTAS = [
			'ban' => -10
		];

		public $username;
		public $password;
		public $name;
		public $address;
		public $mail_pri;
		public $mail_sec;
		public $reputation;
		public $privilege;

		public function __construct($state, $hashed = false) {

			if (!empty($state['password']) && !$hashed)
				$state['password'] = password_hash($state['password'], PASSWORD_DEFAULT);

			parent::__construct($state);
			$this->username = $this->validateString('username');
			$this->password = $this->validateString('password');
			$this->name = $this->validateString('name', required: false);
			$this->address = $this->validateString('address', required: false);
			$this->mail_pri = $this->validateString('mail_pri', required: false);
			$this->mail_sec = $this->validateString('mail_sec', required: false);
			$this->reputation = $this->validateNumeric('reputation', required: false);
			$this->privilege = $this->validateNumeric('privilege', required: false);

			$this->checkValidation();
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