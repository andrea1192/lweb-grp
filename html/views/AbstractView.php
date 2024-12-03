<?php namespace views;

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


		protected function generateMainMenu() {
			return <<<EOF
			<ul class="menu">
				<li><a href="movies.php?action=list_movies">Movies</a></li>
				<li><a href="movies.php?action=list_requests">Submissions</a></li>
			</ul>
			EOF;
		}

		protected function generateUserMenu() {

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

				if ($this->session->isMod()) {
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

		public function printHeader() {
			$main_menu = $this->generateMainMenu();
			$user_menu = $this->generateUserMenu();

			echo <<< EOF
			<div id="top"></div>
			<div id="header">
				<div class="flex cross-center wrapper">
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

		public function printFooter() {
			require_once('connection.php');
			$credits = CREDITS;
			$snackbar = ($this->session->holdsNotification()) ?
					UIComponents::getSnackbar($this->session->popNotification()) : '';

			echo <<<EOF
			{$snackbar}
			<div id="footer" class="bottom">
				<div class="wrapper flex cross-center">
					<div id="validation"></div>
					<div id="credits">{$credits}</div>
					<a id="btt" class="right" href="#top">Back to top</a>
				</div>
			</div>
			EOF;
		}

		public static function validateHTML($buffer) {
			$button = '';

			$document = new \DOMDocument();
			$document->preserveWhiteSpace = false;
			$document->formatOutput = true;
			$success = $document->loadXML($buffer);

			if (!$success) {
				$tooltip = UIComponents::getTooltip(
						"The document couldn't be validated according to its DOCTYPE"
				);
				$button = UIComponents::getTextButton(
						'ERRORS',
						'error',
						enabled: false,
						cls: 'colored-red',
						content: $tooltip
				);

				return str_replace('<div id="validation"></div>', $button, $buffer);
			}

			$doctype = $document->doctype->publicId;

			if ($document->validate()) {
				$tooltip = UIComponents::getTooltip(
						"This document is <strong>valid</strong> according to its DOCTYPE: {$doctype}"
				);
				$button = UIComponents::getTextButton(
						'VALID',
						'check_circle',
						enabled: false,
						cls: 'colored-green',
						content: $tooltip
				);

			} else {
				$tooltip = UIComponents::getTooltip(
						"This document is <strong>invalid</strong> according to its DOCTYPE: {$doctype}"
				);
				$button = UIComponents::getTextButton(
						'ERRORS',
						'error',
						enabled: false,
						cls: 'colored-red',
						content: $tooltip
				);
			}

			$validation = $document->createDocumentFragment();
			$validation->appendXML($button);

			$footer = $document->getElementById('validation')->replaceWith($validation);

			return static::getXML($document);
		}

		private static function getXML($document) {
			$xml = str_replace(
					"    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n",
					'',
					$document->saveXML());

			return $xml;
		}

		protected function getMapper($mapper) {
			return \controllers\ServiceLocator::resolve($mapper);
		}
	}
?>