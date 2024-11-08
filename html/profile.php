<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class ProfileController extends AbstractController {

		public function route() {

			// Livello di privilegio richiesto: login eseguito
			if (!$this->session->isLoggedIn())
				header('Location: index.php');

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\ProfileView($this->session);
					$view->render();
					break;

				case 'save':
					if (isset($_POST)) {
						$password = static::sanitize($_POST['password']);

						$user = $this->session->getUser();
						$mapper = ServiceLocator::resolve('users');

						if ($user && password_verify($password, $user->password)) {
							$user->name = static::sanitize($_POST['name']);
							$user->address = static::sanitize($_POST['address']);
							$user->mail_pri = static::sanitize($_POST['mail_pri']);
							$user->mail_sec = static::sanitize($_POST['mail_sec']);

							$mapper->update($user);
							$this->session->pushNotification(
									'Changes saved.');
						} else {
							$this->session->pushNotification(
									'Invalid password. Please try again.');
						}
					}

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'change_password':
					$view = new \views\PasswordChangeView($this->session);
					$view->render();
					break;

				case 'save_password':
					if (isset($_POST)) {
						$password = static::sanitize($_POST['password']);
						$password_new = static::sanitize($_POST['password_new']);
						$password_confirm = static::sanitize($_POST['password_confirm']);

						$user = $this->session->getUser();
						$mapper = ServiceLocator::resolve('users');

						if ($user
								&& password_verify($password, $user->password)
								&& $password_new === $password_confirm) {
							$user->password = password_hash($password_new, PASSWORD_DEFAULT);

							$mapper->update($user);
							$this->session->pushNotification(
									'Password saved.');
							$redir = 'profile.php';

						} elseif (!password_verify($password, $user->password)) {
							$this->session->pushNotification(
									'Invalid password. Please try again.');
							$redir = $_SERVER['HTTP_REFERER'];

						} elseif ($password_new !== $password_confirm) {
							$this->session->pushNotification(
									'Passwords not matching. Please try again.');
							$redir = $_SERVER['HTTP_REFERER'];
						}
					}

					header("Location: $redir");
					break;

				case 'confirm_delete':
					$view = new \views\AccountDeleteView($this->session);
					$view->render();
					break;

				case 'delete_account':
					if (isset($_POST)) {
						$password = static::sanitize($_POST['password']);

						$user = $this->session->getUser();
						$mapper = ServiceLocator::resolve('users');

						if ($user && password_verify($password, $user->password)) {
							$user->setPrivilege(-1);
							$mapper->update($user);
							$this->session->setUser(null);
							$this->session->pushNotification(
									'Account deleted :-(');
							$redir = 'index.php';
						} else {
							$this->session->pushNotification(
									'Invalid password. Please try again.');
							$redir = $_SERVER['HTTP_REFERER'];
						}
					}

					header("Location: $redir");
					break;
			}
		}
	}

	new ProfileController();
?>