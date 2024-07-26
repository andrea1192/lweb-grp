<?php namespace views;

	abstract class AbstractView {
		protected $session;

		public function __construct($session) {
			$this->session = $session;
		}

		public static function factoryMethod($session, $model) {
			$class = get_class($model);
			$parents = class_parents($model);
			array_unshift($parents, $class);

			foreach ($parents as $parent) {
				$view = preg_replace('/models/', 'views', $parent, limit: 1);

				if (class_exists($view)) {
					return new $view($session, $model);
				}
			}
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