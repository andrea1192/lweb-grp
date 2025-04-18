<?php namespace views;

	/* Classe base per viste di tipo dialogo, che consentono un'operazione specifica */
	abstract class DialogView extends AbstractView {

		public function render() {
			require_once('templates/DialogTemplate.php');
		}
	}

	/* Script di installazione */
	class SetupView extends DialogView {

		public function printTitle() {
			print("Setup - grp");
		}

		/* Override del menu principale per rimuovere le sezioni, non ancora popolate */
		protected function generateMainMenu() {
			return '';
		}

		/* Override del menu utente per rimuovere pulsante di login, non ancora funzionante */
		protected function generateUserMenu() {
			return 'grp install script';
		}

		public function printDialog() {
			require_once('connection.php');

			$action_install = 'install.php?action=install';
			$action_restore = 'install.php?action=restore';
			$tooltip_install = 'Carry out selected actions';
			$tooltip_restore = 'Empty database tables';

			$samples_count = 0;
			$users_count = 0;
			$users_table = '';
			$components = '\views\UIComponents';

			foreach (SAMPLE_CONTENT as $category=>$items)
				$samples_count += count($items);

			foreach (BUILTIN_USERS as $details) {
				$user = new \models\User($details);
				$users_count++;
				$users_table .= <<<EOF
				<tr>
					<td>{$details['username']}</td>
					<td>{$details['password']}</td>
					<td>{$user->getUserType()}</td>
				</tr>
				EOF;
			}

			if ($users_count)
				$users_table = <<<EOF
					<table>
						<tr>
							<th>Username</th>
							<th>Password</th>
							<th>Privileges</th>
						</tr>
						{$users_table}
					</table>
					EOF;
			else
				$users_table = '';

			$optionals = <<<EOF
				<div class="flex column">
					{$components::getCheckbox(
							'Set up database tables',
							'setup_tables',
							checked: true,
							enabled: false)}
					{$components::getCheckbox(
							"Set up sample content ({$samples_count} items)",
							'setup_sample',
							checked: $samples_count,
							enabled: $samples_count)}
					{$components::getCheckbox(
							"Set up built-in users ({$users_count} accounts)",
							'setup_users',
							checked: $users_count,
							enabled: $users_count)}
					{$users_table}
				</div>
			EOF;

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$action_install}" method="post">
				<div>{$components::getIcon('database')}</div>
				<h1>Setup</h1>
				<div id="fields" class="flex column">
					<span class="prompt">Data extracted from <em>connection.php</em></span>
					{$components::getTextInput(
							'Database host',
							'db_host',
							DB_HOST,
							supp: 'Hostname or IP address',
							enabled: false)}
					{$components::getTextInput(
							'Database name',
							'db_name',
							DB_NAME,
							supp: 'This database must already exist',
							enabled: false)}
					{$components::getTextInput(
							'Username',
							'db_user',
							DB_USER,
							supp: 'This user must have CREATE privileges on the database',
							enabled: false)}
					{$components::getTextInput(
							'Password',
							'db_pass',
							DB_PASS,
							enabled: false)}
					{$optionals}
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$components::getTextButton(
								'Restore',
								'settings_backup_restore',
								$action_restore,
								cls: 'colored-red',
								content: $components::getTooltip($tooltip_restore)
						)}
					</div>
					<div class="flex right">
						{$components::getFilledButton(
								'Install',
								'database_upload',
								content: $components::getTooltip($tooltip_install)
						)}
					</div>
				</div>
			</form>
			EOF;
		}
	}

	/* Form di login */
	class SigninView extends DialogView {

		public function printTitle() {
			print("Sign in - grp");
		}

		public function printDialog() {
			$signin = 'login.php?action=verify';
			$signup = 'login.php?action=signup';

			$components = '\views\UIComponents';

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$signin}" method="post">
				<div>{$components::getIcon('login')}</div>
				<h1>Sign in</h1>
				<div id="fields" class="flex column">
					{$components::getTextInput('Username', 'username')}
					{$components::getPasswordInput('Password', 'password')}
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$components::getTextButton(
								'Create account',
								'',
								$signup,
								cls:'colored-blue'
						)}
					</div>
					<div class="flex right">
						{$components::getFilledButton('Sign in', '')}
					</div>
				</div>
			</form>
			EOF;
		}
	}

	/* Form di registrazione */
	class SignupView extends DialogView {

		public function printTitle() {
			print("Create account - grp");
		}

		public function printDialog() {
			$action = 'login.php?action=save';

			$components = '\views\UIComponents';
			$errors = ($this->session->holdsErrors()) ? $this->session->popErrors() : [];

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$action}" method="post">
				<div>{$components::getIcon('account_circle')}</div>
				<h1>Create account</h1>
				<div id="fields" class="flex column">
					{$components::getTextInput(
							'Username*', 'username', $_POST['username'] ?? '', errors: $errors)}
					{$components::getPasswordInput(
							'Password*', 'password', $_POST['password'] ?? '', errors: $errors)}
					{$components::getTextInput(
							'Name', 'name', $_POST['name'] ?? '')}
					{$components::getTextInput(
							'Address', 'address', $_POST['address'] ?? '')}
					{$components::getTextInput(
							'Primary e-mail', 'mail_pri', $_POST['mail_pri'] ?? '')}
					{$components::getTextInput(
							'Secondary e-mail', 'mail_sec', $_POST['mail_sec'] ?? '')}
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$components::getResetButton()}
					</div>
					<div class="flex right">
						{$components::getFilledButton('Create account', 'person_add')}
					</div>
				</div>
			</form>
			EOF;
		}
	}

	/* Form di modifica dei propri dati. Per la conferma è richiesta la password corrente. */
	class ProfileView extends DialogView {
		public $user;

		public function __construct() {
			parent::__construct();

			$this->user = $this->session->getUser();
		}

		public function printTitle() {
			print("Profile: {$this->user->username} - grp");
		}

		public function printDialog() {
			$action = 'profile.php?action=save';
			$confirm_delete = 'profile.php?action=confirm_delete';
			$change_password = 'profile.php?action=change_password';
			$components = '\views\UIComponents';

			$controls_left = UIComponents::getTextButton(
					'Delete account',
					'delete',
					$confirm_delete,
					cls:'colored-red');
			$controls_right = UIComponents::getFilledButton(
					'Save changes',
					'save');

			$view = new User($this->user);
			$view->display($action, $controls_left, $controls_right, confirm: true);

			echo <<<EOF
			<div class="dialog flex cross-center">
				<span>Other options:</span>
				{$components::getTextButton(
						'Change password',
						'password',
						$change_password,
						cls: 'colored-blue right')}
			</div>
			EOF;
		}
	}

	/* Form di modifica della password. Per la conferma è richiesta la password corrente. */
	class PasswordChangeView extends ProfileView {
		public $password;

		public function __construct($password = null) {
			parent::__construct();

			$this->password = $password;
		}

		public function printTitle() {
			print("Change password: {$this->user->username} - grp");
		}

		public function printDialog() {
			$action = 'profile.php?action=save_password';

			$components = '\views\UIComponents';
			$errors = ($this->session->holdsErrors()) ? $this->session->popErrors() : [];

			if (empty($this->password))
				$current_password_field = UIComponents::getPasswordInput(
						'Current password',
						'password',
						errors: $errors
				);
			else
				$current_password_field = UIComponents::getHiddenInput(
						'password',
						value: $this->password
				);

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$action}" method="post">
				<div>{$components::getIcon('password')}</div>
				<h1>Change password</h1>
				<div id="fields" class="flex column">
					{$current_password_field}
					{$components::getPasswordInput(
							'New password',
							'password_new',
							errors: $errors
					)}
					{$components::getPasswordInput(
							'Confirm new password',
							'password_confirm',
							errors: $errors
					)}
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$components::getResetButton()}
					</div>
					<div class="flex right">
						{$components::getFilledButton('Save changes', 'save')}
					</div>
				</div>
			</form>
			EOF;
		}
	}

	/* Dialogo di cancellazione dell'account. Per la conferma è richiesta la password corrente. */
	class AccountDeleteView extends ProfileView {

		public function printTitle() {
			print("Delete account: {$this->user->username} - grp");
		}

		public function printDialog() {
			$action = 'profile.php?action=delete_account';

			$components = '\views\UIComponents';
			$errors = ($this->session->holdsErrors()) ? $this->session->popErrors() : [];

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$action}" method="post">
				<div>{$components::getIcon('delete')}</div>
				<h1>Delete account</h1>
				<div id="fields" class="flex column">
					<span>Are you sure you want to delete your account?</span>
					<div class="flex column">
						<span class="prompt">Enter your current password to confirm:</span>
						{$components::getPasswordInput('Password', 'password', errors: $errors)}
					</div>
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$components::getTextButton(
								'Cancel',
								'',
								$_SERVER['HTTP_REFERER'],
								cls: 'colored-blue')}
					</div>
					<div class="flex right">
						{$components::getFilledButton(
								'Delete',
								'delete',
								cls: 'filled-red')}
					</div>
				</div>
			</form>
			EOF;
		}
	}

	/* Form di modifica dei dati di un altro utente */
	class UserEditView extends DialogView {
		public $user;

		public function __construct($user) {
			parent::__construct();

			$this->user = \controllers\ServiceLocator::resolve('users')->read($user);
		}

		public function printTitle() {
			print("Edit user: {$this->user->username} - grp");
		}

		public function printDialog() {
			$view = new User($this->user);
			$action = $view->generateURL('update');
			$components = '\views\UIComponents';

			$controls_left = $view->generateResetButton();
			$controls_right = UIComponents::getFilledButton(
					'Save changes',
					'save'
			);

			$view->display($action, $controls_left, $controls_right);
		}
	}

	/* Visualizzazione del link di reset della password appena generato */
	class PasswordResetView extends UserEditView {
		public $reset_URL;

		public function __construct($user, $reset_URL) {
			parent::__construct($user);

			$this->reset_URL = $reset_URL;
		}

		public function printTitle() {
			print("Password reset: {$this->user->username} - grp");
		}

		public function printDialog() {
			$components = '\views\UIComponents';

			echo <<<EOF
				<div class="dialog flex column">
					<div>{$components::getIcon('password')}</div>
					<h1>Password reset</h1>

					<div>The password has been reset for user <em>{$this->user->username}</em>.
					With this link they will be able to sign in and set a new password for their
					account:</div>
					<samp>$this->reset_URL</samp>
					<small>The link <strong>will only be displayed once</strong></small>

					<div id="controls" class="flex">
					<div class="flex right">
						{$components::getTextButton(
								'OK',
								'',
								href: htmlspecialchars($_SERVER['HTTP_REFERER']),
								cls: 'colored-blue'
						)}
					</div>
				</div>
				</div>
				EOF;
		}
	}
?>