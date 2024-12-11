<?php namespace controllers;

	class Session {
		private $user;

		public function __construct() {
			// Inizializza una nuova sessione o ne riprende una avviata in precedenza
			session_start();

			// Se le variabili di sessione contengono un username, utilizzalo come utente corrente
			if (isset($_SESSION['username']))
				$this->setUser($_SESSION['username']);
		}

		/* Restituisce l'istanza di User che rappresenta l'utente corrente */
		public function getUser() {
			return $this->user;
		}

		/* Carica $username come utente corrente, o lo reimposta con NULL */
		public function setUser($username) {
			if ($username) {
				$this->user = ServiceLocator::resolve('users')->getUserByUsername($username);

				$_SESSION['username'] = $username;
			} else {

				unset($_SESSION['username']);
			}
		}

		public function getUsername() {
			return ($this->isLoggedIn()) ? $this->user->username : 'Visitor';
		}

		public function getReputation() {
			return ($this->isLoggedIn()) ? $this->user->reputation : 0;
		}

		public function getUserType() {
			return ($this->isLoggedIn()) ? $this->user->getUserType() : 'Guest';
		}

		/* Restituisce true se è stato effettuato l'accesso (var. sessione 'username' impostata) */
		public function isLoggedIn() {
			return isset($_SESSION['username']);
		}

		/* Restituisce true se l'utente ha i privilegi per pubblicare contenuti (non è bandito) */
		public function isAllowed() {
			return (($this->isLoggedIn()) && ($this->user->isAllowed()));
		}

		/* Restituisce true se l'utente ha i privilegi di moderatore, o superiori */
		public function isMod() {
			return (($this->isLoggedIn()) && ($this->user->isMod()));
		}

		/* Restituisce true se l'utente ha i privilegi di amministratore, o superiori */
		public function isAdmin() {
			return (($this->isLoggedIn()) && ($this->user->isAdmin()));
		}

		/* Restituisce true se l'utente è autore dell'oggetto $object (post, reazione...) */
		public function isAuthor($object) {
			return (($this->isLoggedIn()) && ($this->user->isAuthor($object)));
		}

		/* Restituisce true se la sessione contiene un messaggio da presentare all'utente */
		public function holdsNotification() {
			return isset($_SESSION['notification']);
		}

		/* Salva un messaggio da presentare all'utente nella sessione corrente */
		public function pushNotification($message) {
			$_SESSION['notification'] = $message;
		}

		/* Restituisce il messaggio contenuto nella sessione corrente e lo reimposta */
		public function popNotification() {
			$notification = $_SESSION['notification'];
			unset($_SESSION['notification']);

			return $notification;
		}

		/* Restituisce true se la sessione contiene una lista di errori (es. campi non validi) */
		public function holdsErrors() {
			return isset($_SESSION['errors']);
		}

		/* Salva una lista di errori da presentare all'utente nella sessione corrente */
		public function pushErrors($errors) {
			$_SESSION['errors'] = $errors;
		}

		/*  Restituisce la lista di errori contenuta nella sessione corrente e la reimposta */
		public function popErrors() {
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);

			return $errors;
		}
	}
?>