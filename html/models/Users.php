<?php namespace models;

	require_once('connection.php');
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

		public function query($query, $parameters) {
			$stmt = $this->connection->prepare($query);
			$stmt->execute($parameters);

			$result = $stmt->get_result();

			if ($result)
				return $result->fetch_assoc();
			else
				return $result;
		}
	}

	class Users extends Database {

		public function getUserByUsername($username) {
			return $this->select($username);
		}

		public function select($username) {
			$query = 'SELECT * FROM Users WHERE username = ?';
			$match = $this->query($query, [$username]);

			if ($match)
				return new User($match);
		}

		public function insert($user) {
			$state = $user->getState();

			$values = array_values($state);
			$parameters = implode(',', array_keys($state));
			$placeholders = implode(',', array_fill(0, count($state), '?'));

			$query = "INSERT INTO Users ($parameters) VALUES ({$placeholders})";

			$this->query($query, $values);
		}

		public function update($username, $user) {
			$current = $this->select($username);
			$diff = array_diff_assoc($user->getState(), $current->getState());

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