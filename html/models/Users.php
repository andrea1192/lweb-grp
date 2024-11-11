<?php namespace models;

	require_once('connection.php');
	require_once('models/Repository.php');
	require_once('models/User.php');

	class Database {
		protected $connection;

		public function __construct() {
			$this->connection = new \mysqli(
					DB_HOST,
					DB_USER,
					DB_PASS,
					DB_NAME
			);
		}

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

	class Users extends Database implements IRepository {

		public function getUserByUsername($username) {
			return $this->read($username);
		}

		public function read($id) {
			$query = 'SELECT * FROM Users WHERE username = ?';
			$match = $this->query($query, [$id]);

			if ($match)
				return new User($match);
		}

		public function readAll() {
			$query = 'SELECT * FROM Users';
			$matches = $this->query($query);

			foreach ($matches as $match) {
				$users[] = new User($match);
			}

			return $users;
		}

		public function create($type, $state) {
			$user = new \models\User($state);

			$values = array_values($state);
			$parameters = implode(',', array_keys($state));
			$placeholders = implode(',', array_fill(0, count($state), '?'));

			$query = "INSERT INTO Users ($parameters) VALUES ({$placeholders})";

			$this->query($query, $values);
		}

		public function update($object) {
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

			$query = "UPDATE Users SET $parameters WHERE username = ?";

			$this->query($query, $values);
		}
	}
?>