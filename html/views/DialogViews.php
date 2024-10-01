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
			$signup = 'login.php?action=signup';

			$components = '\views\UIComponents';

			echo <<<EOF
			<form id="login" class="dialog flex column" action="" method="get">
				<div>{$components::getIcon('login')}</div>
				<h1>Sign in</h1>
				<div id="fields" class="flex column">
					{$components::getTextInput('Username', 'user')}
					{$components::getPasswordInput('Password', 'pass')}
				</div>
				<div id="controls" class="flex">
					<div class="flex left align">
						{$components::getTextButton('Create account', '', $signup, cls:'colored-blue')}
					</div>
					<div class="flex right align">
						{$components::getFilledButton('Sign in', '', '#')}
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
			$components = '\views\UIComponents';

			echo <<<EOF
			<form id="login" class="dialog flex column" action="" method="get">
				<div>{$components::getIcon('account_circle')}</div>
				<h1>Create Account</h1>
				<div id="fields" class="flex column">
					{$components::getTextInput('Username*', 'user')}
					{$components::getPasswordInput('Password*', 'pass')}
					{$components::getTextInput('Name', 'name')}
					{$components::getTextInput('Address', 'address')}
					{$components::getTextInput('Primary e-mail', 'mail_pri')}
					{$components::getTextInput('Secondary e-mail', 'mail_sec')}
				</div>
				<div id="controls" class="flex">
					<div class="flex left align">
						{$components::getTextButton('Reset', 'refresh', '#', cls:'colored-blue')}
					</div>
					<div class="flex right align">
						{$components::getFilledButton('Create account', 'person_add', '#')}
					</div>
				</div>
			</form>
			EOF;
		}
	}

	class ProfileView extends DialogView {
		public $user;

		public function __construct($session) {
			parent::__construct($session);

			$this->user = $this->session->getUsername();
		}

		public function printTitle() {
			print("Profile: {$this->user} - grp");
		}

		public function printDialog() {
			$components = '\views\UIComponents';

			echo <<<EOF
			<form id="login" class="dialog flex column" action="" method="get">
				<div>{$components::getIcon('account_circle')}</div>
				<h1>{$this->user}</h1>
				<div id="fields" class="flex column">
					{$components::getPasswordInput('Password', 'pass')}
					{$components::getTextInput('Name', 'name')}
					{$components::getTextInput('Address', 'address')}
					{$components::getTextInput('Primary e-mail', 'mail_pri')}
					{$components::getTextInput('Secondary e-mail', 'mail_sec')}
				</div>
				<div id="controls" class="flex">
					<div class="flex left align">
						{$components::getTextButton('Delete account', 'delete', '#', cls:'colored-red')}
					</div>
					<div class="flex right align">
						{$components::getFilledButton('Save changes', 'save', '#')}
					</div>
				</div>
			</form>
			EOF;
		}
	}
?>