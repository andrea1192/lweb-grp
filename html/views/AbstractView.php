<?php namespace views;

	require_once('views/UIComponents.php');

	abstract class AbstractView {
		protected $session;

		public function __construct() {
			$this->session = \controllers\ServiceLocator::resolve('session');
		}

		public static function build($type, $model, $ref) {

			switch ($type) {
				case 'movie':
					return new Movie($model, $ref);
				case 'request':
					return new Request($model, $ref);

				case 'review':
					return new Review($model, $ref);
				case 'question':
					return new Question($model, $ref);
				case 'spoiler':
					return new Spoiler($model, $ref);
				case 'extra':
					return new Extra($model, $ref);
				case 'comment':
					return new Comment($model, $ref);

				case 'like':
					return new Like($model, $ref);
				case 'usefulness':
					return new Usefulness($model, $ref);
				case 'agreement':
					return new Agreement($model, $ref);
				case 'spoilage':
					return new Spoilage($model, $ref);
				case 'answer':
					return new Answer($model, $ref);
				case 'report':
					return new Report($model, $ref);
			}
		}

		public static function matchModel($model, $ref = null) {
			$class = get_class($model);
			$parents = class_parents($model);
			array_unshift($parents, $class);

			foreach ($parents as $parent) {
				$view = preg_replace('/models/', 'views', $parent, limit: 1);

				if (class_exists($view)) {
					if ($ref) {
						return new $view($model, $ref);
					} else {
						return new $view($model);
					}
				}
			}
		}

		protected static function printPrologue() {
			echo '<?xml version="1.0" encoding="UTF-8"?>';
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

				$dropdown_items = UIComponents::getDropdownItem('Profile', 'person', 'profile.php');

				if ($this->session->isAdmin()) {
					$dropdown_items .= UIComponents::getDropdownItem('Users', 'group', 'users.php');
				}

				$dropdown_items .= UIComponents::getDropdownItem('Reports', 'report', 'reports.php');

				$dropdown_items .= UIComponents::getDropdownItem('Sign out', 'logout', 'login.php?action=signout');

				$dropdown_menu = UIComponents::getDropdownMenu($dropdown_items, $dropdown_header);

				return <<<EOF
				<button class="button outlined account">
					<span class="centered initials">{$initials}</span>
					{$dropdown_menu}
				</button>
				EOF;
			} else {

				return UIComponents::getOutlinedButton('Sign in', 'login', 'login.php', cls: 'colored-blue');
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
			$snackbar = ($this->session->holdsNotification()) ?
					UIComponents::getSnackbar($this->session->popNotification()) : '';

			echo <<<EOF
			{$snackbar}
			<div id="footer" class="bottom"><div class="wrapper">Lorem ipsum dolor</div></div>
			EOF;
		}

		protected function getMapper($mapper) {
			return \controllers\ServiceLocator::resolve($mapper);
		}
	}
?>