<?php namespace models;

	require_once('connection.php'); // credenziali di connessione al db

	/* Metodi per interrogare un database relazionale, inserire o aggiornare tuple */
	class Database {
		protected const DB_NAME = DB_NAME;
		protected $connection;

		public function __construct() {
			$this->connection = \controllers\ServiceLocator::resolve('db_connection');
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

		/* Esegue una interrogazione su $table, operando opzionalmente:
		*	- una selezione, specificandone i criteri in $criteria
		*	- una proiezione, specificando gli attributi in $attributes
		*/
		protected function sql_select($table, $criteria = [], $attributes = '*') {
			$values = array_values($criteria);

			if (!empty($criteria)) {
				$criteria = array_keys($criteria);
				$criteria = array_map(fn($elem) => ($elem.'=?'), $criteria);
				$criteria = implode(',', $criteria);

				$query = "SELECT $attributes FROM $table WHERE $criteria";
			} else {
				$query = "SELECT $attributes FROM $table";
			}

			return $this->query($query, $values);
		}

		/* Inserisce una tupla in $table, utilizzando per attributi i valori in $state */
		protected function sql_insert($table, $state) {
			$values = array_values($state);

			$attributes = implode(',', array_keys($state));
			$placeholders = implode(',', array_fill(0, count($state), '?'));

			$query = "INSERT INTO $table ($attributes) VALUES ($placeholders)";
			$this->query($query, $values);
		}

		/* Aggiorna in $table le tuple che rispondono a $criteria, utilizzando i valori in $diff */
		protected function sql_update($table, $criteria, $diff) {
			$values = array_values($diff);
			$values = array_merge($values, array_values($criteria));

			$attributes = array_keys($diff);
			$attributes = array_map(fn($elem) => ($elem.'=?'), $attributes);
			$attributes = implode(',', $attributes);

			$criteria = array_keys($criteria);
			$criteria = array_map(fn($elem) => ($elem.'=?'), $criteria);
			$criteria = implode(',', $criteria);

			$query = "UPDATE $table SET $attributes WHERE $criteria";
			$this->query($query, $values);
		}

		/* Genera un ID appropriato per un elemento di tipo $type */
		protected function generateID($type) {
			$nodes = $this->readAll();
			$prefix = \models\AbstractModel::getPrefix($type);

			if (!$nodes)
				return $prefix.'1';

			$largest = 0;

			foreach ($nodes as $node) {
				preg_match('/([[:alpha:]]+)([[:digit:]])/', $node->id, $matches);

				$pref = $matches[1];
				$number = $matches[2];

				if ($number > $largest)
					$largest = $number;
			}

			return $prefix.++$largest;
		}
	}

	/* Metodi per interrogare una tabella specifica del database, ritornando elementi del dominio */
	class Table extends Database implements IRepository {

		/* Ripristina il repository */
		public function restore() {
			if (!empty(static::DB_TABLE)) {
				$table = static::DB_TABLE;
				$this->query("TRUNCATE $table");
			}
		}

		/* Recupera l'elemento identificato da $id dal repository */
		public function read($id) {
			$table = !empty(static::DB_TABLE) ? static::DB_TABLE : static::DB_VIEW;
			$type = static::OB_TYPE;
			$id_key = static::OB_PRI_KEY;
			$id_value = $id;

			$match = $this->sql_select($table, [$id_key => $id_value]);

			if ($match) {
				return \models\AbstractModel::build($type, $match);
			}
		}

		/* Recupera tutti gli elementi contenuti nel repository */
		public function readAll() {
			$table = !empty(static::DB_TABLE) ? static::DB_TABLE : static::DB_VIEW;
			$type = static::OB_TYPE;
			$id_key = static::OB_PRI_KEY;

			$matches = $this->sql_select($table);

			if (!array_is_list($matches)) {
				return [\models\AbstractModel::build($type, $matches)];
			}

			$objects = [];

			foreach ($matches as $match) {
				$objects[] = \models\AbstractModel::build($type, $match);
			}

			return $objects;
		}

		/* Crea un nuovo elemento di tipo $type, usando $state, e lo aggiunge al repository */
		public function create($type, $state) {
			$table = static::DB_TABLE;

			// Genera ID alfanumerico appropriato
			if (property_exists(\models\AbstractModel::get($type), 'id'))
				$state['id'] = $this->generateID($type);

			// Aggiunge username dell'autore (utente corrente), necessario per alcuni elementi
			if (property_exists(\models\AbstractModel::get($type), 'author'))
				$state['author'] = \controllers\ServiceLocator::resolve('session')->getUsername();

			if (defined(get_parent_class($this).'DB_TABLE')) {
				$pc = get_parent_class($this);
				$pi = new $pc();

				$pi->create($type, $state);
			}

			$object = \models\AbstractModel::build($type, $state);
			$this->sql_insert($table, $object->getAttributes(static::DB_ATTRIBS));

			return $object;
		}

		/* Aggiorna l'elemento $object, identificandolo attraverso la sua chiave primaria */
		public function update($object) {
			$table = static::DB_TABLE;
			$id_key = static::OB_PRI_KEY;
			$id_value = $object->$id_key;

			if (defined(get_parent_class($this).'DB_TABLE')) {
				$pc = get_parent_class($this);
				$pi = new $pc();

				$pi->update($object);
			}

			$current = $this->read($id_value);
			$new = $object;
			$diff = array_diff_assoc(
					$new->getAttributes(static::DB_ATTRIBS),
					$current->getAttributes(static::DB_ATTRIBS));

			if (!empty($diff))
				$this->sql_update($table, [$id_key => $id_value], $diff);

			return $current;
		}
	}

?>