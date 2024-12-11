<?php namespace views;

	/* Visualizzazione di un oggetto di tipo \models\User */
	class User extends AbstractView {
		protected $user;
		protected $errors;

		public function __construct($user) {
			parent::__construct();

			$this->user = $user;
			$this->errors = ($this->session->holdsErrors()) ? $this->session->popErrors() : [];
		}

		/* Genera un URL per l'azione richiesta, se ammessa sull'oggetto di riferimento */
		public function generateURL($action = 'display') {
			$URL = 'users.php';

			if ($this->user)
				$URL .= "?id={$this->user->username}";

			switch ($action) {
				default:
				case 'display':
					break;
				case 'edit':
				case 'update':
				case 'ban':
				case 'unban':
					$URL .= "&action={$action}";
					break;
			}

			return htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}

		/* Visualizza la selezione di dettagli dell'utente generata dal metodo generateSummary() */
		public function displaySummary() {
			echo $this->generateSummary();
		}

		/* Visualizza la selezione di dettagli dell'utente generata dal metodo generateSummary(),
		* insieme ad un pulsante primario per per il ban ed uno secondario per raggiungere una
		* pagina con ulteriori dettagli
		*/
		public function displayListItem() {
			$action_edit = $this->generateURL('edit');

			$summary = $this->generateSummary();
			$quick_edit = $this->session->isAdmin() ?
					UIComponents::getTextButton('Edit', 'edit', href: $action_edit) : '';
			$quick_action = $this->generateBanButton();

			$components = 'views\UIComponents';

			echo <<<EOF
			<div class="post">
				<div class="header">
					{$summary}
					<div class="flex right">
						{$quick_edit}
						{$quick_action}
					</div>
				</div>
			</div>
			EOF;
		}

		/* Form di modifica dei dettagli dell'utente */
		public function display($action = '',
				$controls_left = '',
				$controls_right = '',
				$confirm = false
		) {
			$components = '\views\UIComponents';

			$confirm_prompt = $confirm ? <<<EOF
					<div class="flex column">
						<span class="prompt">Enter your current password to confirm:</span>
						{$components::getPasswordInput('Password', 'confirm_password', errors: $this->errors)}
					</div>
					EOF : '';

			$components = 'views\UIComponents';
			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$action}" method="post">
				<div>{$components::getIcon('account_circle')}</div>
				<h1>{$this->user->username}</h1>
				<div id="fields" class="flex column">
					{$components::getTextInput(
							'Name', 'name', $this->user->name)}
					{$components::getTextInput(
							'Address', 'address', $this->user->address)}
					{$components::getTextInput(
							'Primary e-mail', 'mail_pri', $this->user->mail_pri)}
					{$components::getTextInput(
							'Secondary e-mail', 'mail_sec', $this->user->mail_sec)}
					{$confirm_prompt}
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$controls_left}
					</div>
					<div class="flex right">
						{$controls_right}
					</div>
				</div>
			</form>
			EOF;
		}

		/* Genera il pulsante di ban/unban dell'utente, abilitato a seconda dei privilegi */
		public function generateBanButton() {
			$action_ban = $this->generateURL('ban');
			$action_unban = $this->generateURL('unban');

			$tooltip = UIComponents::getTooltip('You don\'t have sufficient privileges to ban this user.');

			if ($this->user->privilege <= 0) {
				return UIComponents::getTextButton('Unban',
						'lock_open',
						href: $action_unban,
						cls: 'colored-green');

			} elseif ($this->user->privilege < $this->session->getUser()->privilege) {
				return UIComponents::getTextButton('Ban',
						'lock',
						href: $action_ban,
						cls: 'colored-red');

			} else {
				return UIComponents::getTextButton('Ban',
							'lock',
							enabled: false,
							cls: 'colored-grey',
							content: $tooltip);
			}
		}

		/* Genera il codice per visualizzare username, tipologia e reputazione dell'utente */
		protected function generateSummary() {
			$initial = $this->user->username[0];

			return <<<EOF
			<div class="featured">
				<span class="centered initials">{$initial}</span>
			</div>
			<div class="details">
				<h1>{$this->user->username}</h1>
				<div class="flex small">
					<span>{$this->user->getUserType()}</span>
					<span>Reputation {$this->user->reputation}</span>
				</div>
			</div>
			EOF;
		}
	}