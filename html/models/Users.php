<?php namespace models;

	/* Rappresenta il repository degli utenti */
	class Users extends Table {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Users';
		protected const DB_ATTRIBS = [
			'username',
			'password',
			'name',
			'address',
			'mail_pri',
			'mail_sec',
			'reputation',
			'privilege'
		];
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Users (
			username	VARCHAR(160)	PRIMARY KEY,
			password	VARCHAR(160)	NOT NULL,
			name		VARCHAR(160),
			address		VARCHAR(160),
			mail_pri	VARCHAR(160),
			mail_sec	VARCHAR(160),
			reputation	INTEGER			NOT NULL DEFAULT 1,
			privilege	INTEGER			NOT NULL DEFAULT 1,
			CONSTRAINT priv_levels CHECK (privilege BETWEEN -1 AND 3)
		)
		EOF;
		protected const OB_TYPE = 'user';
		protected const OB_PRI_KEY = 'username';

		public function getUserByUsername($username) {
			return $this->read($username);
		}

		/* Recupera l'elemento identificato da $id dal repository */
		public function read($id) {
			$table = static::DB_TABLE;
			$matches = $this->sql_select($table, ['username' => $id]);

			if (count($matches) == 1)
				return new User($matches[0], hashed: true);
		}

		/* Recupera tutti gli elementi contenuti nel repository */
		public function readAll() {
			$table = static::DB_TABLE;
			$matches = $this->sql_select($table);

			$users = [];

			foreach ($matches as $match) {
				$users[] = new User($match, hashed: true);
			}

			return $users;
		}
	}
?>