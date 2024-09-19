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

				$dropdown_items = UIComponents::getDropdownItem('Profile', 'person', 'profile.php');

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

				return UIComponents::getOutlinedButton('Sign in', 'login', 'login.php', cls: 'colored');
			}
		}

		protected function generateReactionButtons() {
			$reaction_types = ($this->post ?? $this->reaction)->reactions;
			$buttons = '';

			if (!$reaction_types) return '';

			foreach ($reaction_types as $type => $stats) {

				if (!$this->session->isLoggedIn())
					$login_prompt = '<div class="tooltip">Sign in to react</div>';
				else
					$login_prompt = '<div class="tooltip">Your account has been disabled</div>';

				$status = $this->session->isAllowed();

				switch ($type) {
					case 'like':
						$buttons .= UIComponents::getTextButton(
								$stats->count_up,
								'thumb_up',
								enabled: $status,
								content: $status ? '' : $login_prompt);
						$buttons .= UIComponents::getTextButton(
								$stats->count_down,
								'thumb_down',
								enabled: $status,
								content: $status ? '' : $login_prompt);
						break;

					case 'usefulness':
						$tooltip = <<<EOF
						<div class="tooltip">
							<span class="material-symbols-outlined"></span>Useful?
							<span class="rate">
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
							</span>
						</div>
						EOF;
						$buttons .= UIComponents::getTextButton(
								$stats->average,
								'lightbulb',
								enabled: $status,
								content: $status ? $tooltip : $login_prompt);
						break;

					case 'agreement':
						$tooltip = <<<EOF
						<div class="tooltip">
							<span class="material-symbols-outlined"></span>Agree?
							<span class="rate">
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
							</span>
						</div>
						EOF;
						$buttons .= UIComponents::getTextButton(
								$stats->average,
								'thumb_up',
								enabled: $status,
								content: $status ? $tooltip : $login_prompt);
						break;

					case 'spoilage':
						$tooltip = <<<EOF
						<div class="tooltip">
							<span class="material-symbols-outlined"></span>Spoiler level:
							<span class="rate">
								<select name="rating">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
								</select>
							</span>
						</div>
						EOF;
						$buttons .= UIComponents::getTextButton(
								$stats->average,
								'speed',
								enabled: $status,
								content: $status ? $tooltip : $login_prompt);
						break;

					default: break;
				}
			}

			return $buttons;
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

		protected function getMapper($mapper) {
			return \controllers\ServiceLocator::resolve($mapper);
		}
	}
?>