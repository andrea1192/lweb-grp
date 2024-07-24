<?php namespace views;

	abstract class AbstractView {
		protected $session;

		public function __construct($session) {
			$this->session = $session;
		}

		protected function printHeader() {

			if ($this->session->isLoggedIn()) {
				print('<div id="header">Logged-in header</div>');
			} else {
				print('<div id="header">Logged-out header</div>');
			}
		}
	}
?>