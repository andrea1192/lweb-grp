<?php namespace models;

	require_once('connection.php'); // credenziali di connessione al db

	/* Metodi per interrogare un database relazionale, inserire o aggiornare tuple */
	class Database {
		protected const DB_NAME = DB_NAME;
		protected $connection;

		public function __construct() {
			$this->connection = \controllers\ServiceLocator::resolve('db_connection');
		}

		/* Esegue una query sul database, restituendo un array con i risultati */
		public function query($query, $parameters = null) {
			$stmt = $this->connection->prepare($query);
			$stmt->execute($parameters);

			$result = $stmt->get_result();

			if ($result) {
				if ($result->num_rows == 1) {
					return [$result->fetch_assoc()];
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
				$criteria = implode(' AND ', $criteria);

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
			$criteria = implode(' AND ', $criteria);

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
				preg_match('/([[:alpha:]]+)([[:digit:]]+)/', $node->id, $matches);

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

		/* Inizializza la tabella, opzionalmente con i dati in un array $source */
		public function init() {

			// Crea le tabelle e/o viste definite per questo elemento (una o più query)
			$this->connection->multi_query(static::DB_SCHEMA);

			// Consuma eventuali output di multi_query prima di continuare
			while ($this->connection->next_result());
		}

		/* Carica dati da un array $source */
		public function load($source) {
			$type = static::OB_TYPE;
			$pkey = static::OB_PRI_KEY;

			$existing_data = [];

			foreach ($source as $attributes) {
				try {
					$this->create($type, $attributes);

				} catch (\mysqli_sql_exception $e) {
					$existing_data[] = $attributes[$pkey];
				}
			}

			if (!empty($existing_data)) {
				$msg = "Couldn't overwrite existing $type elements: ".implode(', ', $existing_data);
				throw new \Exception($msg);
			}
		}

		/* Ripristina la tabella, cancellando una riga alla volta mediante DELETE */
		public function restore() {
			if (!empty(static::DB_TABLE)) {
				$table = static::DB_TABLE;
				$this->query("DELETE FROM $table");
			}
		}

		/* Recupera l'elemento identificato da $id dal repository */
		public function read($id) {
			// Indica una vista appropriata, se disponibile, o la tabella di riferimento. Se
			// questo elemento è distribuito in più tabelle, la vista potrà essere un join con
			// tutti gli attributi.
			$table = !empty(static::DB_VIEW) ? static::DB_VIEW : static::DB_TABLE;
			$type = static::OB_TYPE;
			$pkey = static::OB_PRI_KEY;

			// Controlla da quanti attributi è composta la chiave primaria secondo lo schema, e
			// verifica se quella fornita come argomento è compatibile
			$key_is_array = is_array($pkey);
			$arg_is_array = is_array($id) && (array_keys($id) == $pkey);
			$type_is_compatible = ($key_is_array == $arg_is_array);

			if (!$type_is_compatible)
				return;

			if (!$key_is_array)
				$id = [$pkey => $id];

			$matches = $this->sql_select($table, $id);

			if (count($matches) == 1) {
				return \models\AbstractModel::build($type, $matches[0]);
			}
		}

		/* Recupera tutti gli elementi contenuti nel repository */
		public function readAll() {
			// Indica una vista appropriata, se disponibile, o la tabella di riferimento. Se
			// questo elemento è distribuito in più tabelle, la vista potrà essere un join con
			// tutti gli attributi.
			$table = !empty(static::DB_VIEW) ? static::DB_VIEW : static::DB_TABLE;
			$type = static::OB_TYPE;

			$matches = $this->sql_select($table);
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
			if (property_exists(\models\AbstractModel::get($type), 'id')
					&& empty($state['id']))
				$state['id'] = $this->generateID($type);

			// Aggiunge username dell'autore (utente corrente), necessario per alcuni elementi
			if (property_exists(\models\AbstractModel::get($type), 'author')
					&& empty($state['author']))
				$state['author'] = \controllers\ServiceLocator::resolve('session')->getUsername();

			$parent = get_parent_class($this);

			// Crea l'elemento parente (generalizzazione), se $type distribuito in più tabelle
			if (defined($parent.'::DB_TABLE') && !empty(constant($parent.'::DB_TABLE')))
				(new $parent)->create($type, $state);

			$object = \models\AbstractModel::build($type, $state);
			$this->sql_insert($table, $object->getAttributes(static::DB_ATTRIBS));

			return $object;
		}

		/* Aggiorna l'elemento $object, identificandolo attraverso la sua chiave primaria */
		public function update($object, $base = null) {
			$table = static::DB_TABLE;
			$pkey = static::OB_PRI_KEY;

			// Controlla da quanti attributi è composta la chiave primaria secondo lo schema, e
			// costruisce l'array $id di conseguenza: questo verrà utilizzato prima per recuperare
			// lo stato corrente dell'elemento da aggiornare, poi per selezionarlo nella successiva
			// istruzione SQL UPDATE
			$id = [];

			if (!is_array($pkey))
				$id[$pkey] = $object->$pkey;
			else
				foreach ($pkey as $attrib)
					$id[$attrib] = $object->$attrib;

			$current = $base ?? $this->read(!is_array($pkey) ? $id[$pkey] : $id);
			$new = $object;

			$parent = get_parent_class($this);

			// Aggiorna l'elemento parente (generalizzazione), se $type distribuito in più tabelle
			if (defined($parent.'::DB_TABLE') && !empty(constant($parent.'::DB_TABLE'))) {

				// Utilizza $current come base per il confronto: chiamate ricorsive a read()
				// possono provocare problemi perchè gli elementi restituiti hanno solo parte degli
				// attributi previsti (quelli più generali)
				(new $parent)->update($object, base: $current);
			}

			$diff = array_diff_assoc(
					$new->getAttributes(static::DB_ATTRIBS),
					$current->getAttributes(static::DB_ATTRIBS));

			if (!empty($diff))
				$this->sql_update($table, $id, $diff);

			return $current;
		}
	}

?>