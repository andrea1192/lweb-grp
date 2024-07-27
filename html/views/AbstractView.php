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

		private function generateMainMenu() {
			return <<<EOF
			<ul class="menu">
				<li><a href="movies.php">All Movies</a></li>
				<li><a href="movies.php">Accepted</a></li>
				<li><a href="movies.php">Submitted</a></li>
			</ul>
			EOF;
		}

		private function generateUserMenu() {

			if ($this->session->isLoggedIn()) {
				$initials = substr($this->session->getUsername(), 0, 2);

				return <<<EOF
				<button class="account">
					<span class="centered initials">{$initials}</span>
					<div class="dropdown">
						<ul class="menu">
							<li><a href="" class="flex profile"><span class="material-symbols-outlined"></span><span class="label">Profile</span></a></li>
							<li><a href="" class="flex users"><span class="material-symbols-outlined"></span><span class="label">Users</span></a></li>
							<li><a href="" class="flex report"><span class="material-symbols-outlined"></span><span class="label">Reports</span></a></li>
						</ul>
					</div>
				</button>
				EOF;
			} else {

				return <<<EOF
				<button class="login">
					<span class="material-symbols-outlined"></span><div class="label">Sign in</div>
				</button>
				EOF;
			}
		}

		protected function printHeader() {
			$main_menu = $this->generateMainMenu();
			$user_menu = $this->generateUserMenu();

			echo <<< EOF
			<div id="header">
				<div class="flex wrapper">
					<div class="flex left">
						<div id="logo">grp</div>
						{$main_menu}
					</div>
					<div class="flex right">
						{$user_menu}
					</div>
				</div>
			</div>
			EOF;
		}
	}
?>