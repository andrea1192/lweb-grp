<?php namespace models;

	require_once('connection.php'); // credenziali di connessione al db

	/* Classe base per un repository di tipo database SQL */
	class Database {
		protected const DB_NAME = DB_NAME;
		protected $connection;

		public function __construct() {
			$this->connection = \controllers\ServiceLocator::resolve('db_connection');
		}

		/* Restituisce l'istanza repository di riferimento per gli oggetti di tipo $type, quella
		* che ne implementa le funzionalità di creazione, recupero, aggiornamento e cancellazione
		* su database (CRUD) definite da IRepository
		*/
		public static function getRepo($type) {
			$sl = '\\controllers\\ServiceLocator';

			switch ($type) {
				case 'movie':
				case 'request':

				case 'review':
				case 'question':
				case 'spoiler':
				case 'extra':
				case 'comment':

				case 'like':
				case 'usefulness':
				case 'agreement':
				case 'spoilage':
				case 'answer':
				case 'report':
					return;

				case 'user':
					return $sl::resolve('users');
			}
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

		/* Recupera l'elemento identificato da $id dal repository */
		public function read($id) {
			return $this->getRepo($type)->read($id);
		}

		/* Crea un nuovo elemento di tipo $type, usando $state, e lo aggiunge al repository */
		public function create($type, $state) {
			return $this->getRepo($type)->create($type, $state);
		}

		/* Aggiorna l'elemento $object, identificandolo attraverso la sua chiave primaria */
		public function update($object) {
			return $this->getRepo($type)->update($object);
		}

	}

?>