<?php namespace views;

	require_once('views/AbstractView.php');

	abstract class DialogView extends AbstractView {

		public function render() {
			require_once('templates/DialogTemplate.php');
		}
	}

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
						{$components::getTextButton('Create account', '', $signup, cls:'colored-blue')}
					</div>
					<div class="flex right">
						{$components::getFilledButton('Sign in', '')}
					</div>
				</div>
			</form>
			EOF;
		}
	}

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
			$save = 'profile.php?action=save';
			$confirm_delete = 'profile.php?action=confirm_delete';
			$change_password = 'profile.php?action=change_password';

			$components = '\views\UIComponents';
			$errors = ($this->session->holdsErrors()) ? $this->session->popErrors() : [];

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$save}" method="post">
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

					<div class="flex column">
						<span class="prompt">Enter your current password to confirm:</span>
						{$components::getPasswordInput('Password', 'password', errors: $errors)}
					</div>
				</div>
				<div id="controls" class="flex">
					<div class="flex left">
						{$components::getTextButton(
								'Delete account',
								'delete',
								$confirm_delete,
								cls:'colored-red')}
					</div>
					<div class="flex right">
						{$components::getFilledButton(
								'Save changes',
								'save')}
					</div>
				</div>
			</form>

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

	class PasswordChangeView extends ProfileView {

		public function printTitle() {
			print("Change password: {$this->user->username} - grp");
		}

		public function printDialog() {
			$action = 'profile.php?action=save_password';

			$components = '\views\UIComponents';
			$errors = ($this->session->holdsErrors()) ? $this->session->popErrors() : [];

			echo <<<EOF
			<form id="login" class="dialog flex column" action="{$action}" method="post">
				<div>{$components::getIcon('password')}</div>
				<h1>Change password</h1>
				<div id="fields" class="flex column">
					{$components::getPasswordInput('Current password', 'password', errors: $errors)}
					{$components::getPasswordInput('New password', 'password_new', errors: $errors)}
					{$components::getPasswordInput('Confirm new password', 'password_confirm', errors: $errors)}
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
?>