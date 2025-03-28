<?php namespace models;

	/* Rappresenta il repository degli utenti all'interno di un database SQL */
	class Users extends Database implements IRepository {
		protected const DB_TABLE = 'Users';

		/* Inizializza il repository, da zero od utilizzando i dati in $source */
		public function init($source = null) {
			$table = static::DB_TABLE;

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS $table (
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
					EOF
			);

			if ($source) {
				$existing_users = [];

				foreach ($source as $details) {
					try {
						$this->create('user', $details);

					} catch (\mysqli_sql_exception $e) {
						$existing_users[] = $details['username'];
					}
				}

				if (!empty($existing_users)) {
					$msg = " Couldn't overwrite existing users: ".implode(', ', $existing_users);
					throw new \Exception($msg);
				}
			}
		}

		/* Ripristina il repository */
		public function restore() {
			$table = static::DB_TABLE;
			$this->query("TRUNCATE $table");
		}

		public function getUserByUsername($username) {
			return $this->read($username);
		}

		/* Recupera l'elemento identificato da $id dal repository */
		public function read($id) {
			$table = static::DB_TABLE;
			$match = $this->sql_select($table, ['username' => $id]);

			if ($match)
				return new User($match, hashed: true);
		}

		/* Recupera tutti gli elementi contenuti nel repository */
		public function readAll() {
			$table = static::DB_TABLE;
			$matches = $this->sql_select($table);

			foreach ($matches as $match) {
				$users[] = new User($match, hashed: true);
			}

			return $users;
		}

		/* Crea un nuovo elemento di tipo $type, usando $state, e lo aggiunge al repository */
		public function create($type, $state) {
			$table = static::DB_TABLE;
			$user = new \models\User($state);
			$state = $user->getState();

			$this->sql_insert($table, $state);

			return $user;
		}

		/* Aggiorna l'elemento $object, identificandolo attraverso la sua chiave primaria */
		public function update($object) {
			$table = static::DB_TABLE;
			$username = $object->username;

			$current = $this->read($username);
			$diff = array_diff_assoc($object->getState(), $current->getState());

			if (empty($diff))
				return;

			$this->sql_update($table, ['username' => $username], $diff);

			return $current;
		}
	}
?>