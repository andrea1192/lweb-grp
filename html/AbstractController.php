<?php namespace controllers;

	require_once('session.php');

	abstract class AbstractController {
		protected $session;

		public function __construct() {

			$this->loadSession();
			$this->route();
		}

		protected function loadSession() {

			$this->session = new \controllers\MockSession();
		}
	}
?>