<?php namespace controllers;

	require_once('AbstractController.php');

	class ProfileController extends AbstractController {

		public function route() {

			// Livello di privilegio richiesto: login eseguito
			if (!$this->session->isLoggedIn())
				header('Location: index.php');

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\ProfileView();
					$view->render();
					break;

				// Porta a termine la modifica dei dati dell'utente corrente
				// Per le modifiche iniziate da amministratori, l'azione è *update* in users.php
				case 'save':
					static::checkPOST();
					$password = static::sanitize($_POST['confirm_password']);

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
						static::abort(
								'Invalid password. Please try again.',
								['password' => 'password is invalid']
						);
					}

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'change_password':
					$view = new \views\PasswordChangeView();
					$view->render();
					break;

				case 'save_password':
					static::checkPOST();
					$password = static::sanitize($_POST['password']);
					$password_new = static::sanitize($_POST['password_new']);
					$password_confirm = static::sanitize($_POST['password_confirm']);

					$user = $this->session->getUser();
					$mapper = ServiceLocator::resolve('users');

					if ($user
							&& password_verify($password, $user->password)
							&& !empty($password_new)
							&& $password_new === $password_confirm) {
						$user->password = password_hash($password_new, PASSWORD_DEFAULT);

						$mapper->update($user);
						$this->session->pushNotification(
								'Password saved.');
						$redir = 'profile.php';

					} elseif (!password_verify($password, $user->password)) {
						static::abort(
								'Invalid password. Please try again.',
								['password' => 'password is invalid']
						);

					} elseif (empty($password_new)) {
						static::abort(
								'Passwords are required. Please choose one.',
								[
										'password_new' => 'passwords are required',
										'password_confirm' => 'passwords are required'
								]
						);

					} elseif ($password_new !== $password_confirm) {
						static::abort(
								'Passwords not matching. Please try again.',
								[
										'password_new' => 'passwords do not match',
										'password_confirm' => 'passwords do not match'
								]
						);
					}

					header("Location: $redir");
					break;

				case 'confirm_delete':
					$view = new \views\AccountDeleteView();
					$view->render();
					break;

				case 'delete_account':
					static::checkPOST();
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
						static::abort(
								'Invalid password. Try again?',
								['password' => 'password is invalid']
						);
					}

					header("Location: $redir");
					break;
			}
		}
	}

	new ProfileController();
?>