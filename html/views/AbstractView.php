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
				<li><a href="movies.php?action=list_movies">Movies</a></li>
				<li><a href="movies.php?action=list_requests">Submissions</a></li>
			</ul>
			EOF;
		}

		private function generateUserMenu() {

			if ($this->session->isLoggedIn()) {
				$initials = $this->session->getUsername()[0];
				$username = $this->session->getUsername();

				$html = '<li><a href="" class="flex profile"><span class="material-symbols-outlined"></span><span class="label">Profile</span></a></li>';

				if ($this->session->isAdmin()) {
					$html .= '<li><a href="" class="flex users"><span class="material-symbols-outlined"></span><span class="label">Users</span></a></li>';
				}

				if ($this->session->isMod()) {
					$html .= '<li><a href="" class="flex report"><span class="material-symbols-outlined"></span><span class="label">Reports</span></a></li>';
				}

				$html .= '<li><a href="" class="flex logout"><span class="material-symbols-outlined"></span><span class="label">Sign out</span></a></li>';

				return <<<EOF
				<button class="account">
					<span class="centered initials">{$initials}</span>
					<div class="dropdown">
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
						<ul class="menu">
							{$html}
						</ul>
					</div>
				</button>
				EOF;
			} else {

				return <<<EOF
				<button class="colored login">
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

		protected function printFooter() {
			echo <<<EOF
			<div id="footer" class="bottom"><div class="wrapper">Lorem ipsum dolor</div></div>
			EOF;
		}
	}
?>