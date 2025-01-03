<?php namespace models;

	require_once('connection.php'); // credenziali di connessione al db

	/* Classe base per un repository di tipo database SQL */
	class Database {
		protected $connection;
		protected const DB_NAME = DB_NAME;

		public function __construct() {
			$this->connection = new \mysqli(
					DB_HOST,
					DB_USER,
					DB_PASS,
					DB_NAME
			);
		}

		/* Esegue una query sul database, restituendo un array associativo con i risultati */
		public function query($query, $parameters = null) {
			$stmt = $this->connection->prepare($query);
			$stmt->execute($parameters);

			$result = $stmt->get_result();

			if ($result) {
				if ($result->num_rows == 1) {
					return $result->fetch_assoc();
				} else {
					return $result->fetch_all(MYSQLI_ASSOC);
				}
			}
		}
	}

	/* Rappresenta un repository tipo tabella di database SQL */
	class Users extends Database implements IRepository {
		protected const DB_TABLE = TAB_USERS;

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
			$query = "SELECT * FROM $table WHERE username = ?";
			$match = $this->query($query, [$id]);

			if ($match)
				return new User($match, hashed: true);
		}

		/* Recupera tutti gli elementi contenuti nel repository */
		public function readAll() {
			$table = static::DB_TABLE;
			$query = "SELECT * FROM $table";
			$matches = $this->query($query);

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

			$values = array_values($state);
			$parameters = implode(',', array_keys($state));
			$placeholders = implode(',', array_fill(0, count($state), '?'));

			$query = "INSERT INTO $table ($parameters) VALUES ({$placeholders})";

			$this->query($query, $values);

			return $user;
		}

		/* Crea un nuovo elemento di tipo $type, usando $state, e lo aggiunge al repository */
		public function update($object) {
			$table = static::DB_TABLE;
			$username = $object->username;

			$current = $this->read($username);
			$diff = array_diff_assoc($object->getState(), $current->getState());

			if (empty($diff))
				return;

			$values = array_values($diff);
			$values[] = $current->username;

			$parameters = array_keys($diff);
			$parameters = array_map(fn($elem) => ($elem.'=?'), $parameters);
			$parameters = implode(',', $parameters);

			$query = "UPDATE $table SET $parameters WHERE username = ?";

			$this->query($query, $values);

			return $current;
		}
	}
?>