<?php namespace views;

	require_once('views/UIComponents.php');

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
				<li><a href="movies.php?action=list_movies">Movies</a></li>
				<li><a href="movies.php?action=list_requests">Submissions</a></li>
			</ul>
			EOF;
		}

		private function generateUserMenu() {

			if ($this->session->isLoggedIn()) {
				$initials = $this->session->getUsername()[0];
				$username = $this->session->getUsername();

				$dropdown_header = <<<EOF
				<div class="header">
					<div class="featured">
						<span class="centered initials">{$initials}</span>
					</div>
					<div class="details">
						<h1>{$username}</h1>
						<div class="flex small">
							<span class="privileges">{$this->session->getUserType()}</span>
							<span class="reputation">Reputation {$this->session->getReputation()}</span>
						</div>
					</div>
				</div>
				EOF;

				$dropdown_items = UIComponents::getDropdownItem('Profile', 'person');

				if ($this->session->isAdmin()) {
					$dropdown_items .= UIComponents::getDropdownItem('Users', 'group');
				}

				$dropdown_items .= UIComponents::getDropdownItem('Reports', 'report', 'reports.php');

				$dropdown_items .= UIComponents::getDropdownItem('Sign out', 'logout');

				$dropdown_menu = UIComponents::getDropdownMenu($dropdown_items, $dropdown_header);

				return <<<EOF
				<button class="account">
					<span class="centered initials">{$initials}</span>
					{$dropdown_menu}
				</button>
				EOF;
			} else {

				return UIComponents::getOutlinedButton('Sign in', 'login', '#', cls: 'colored');
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

		protected function printFooter() {
			echo <<<EOF
			<div id="footer" class="bottom"><div class="wrapper">Lorem ipsum dolor</div></div>
			EOF;
		}
	}
?>