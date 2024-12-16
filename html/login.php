<?php namespace controllers;

	require_once('AbstractController.php');

	class LoginController extends AbstractController {

		public function route() {

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'signin':
					$view = new \views\SigninView();
					$view->render();
					break;

				case 'signout':
					$this->session->setUser(null);
					$this->session->pushNotification('Signed out. See you soon!');

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				// Verifica la validità di username e password ed aggiorna la sessione
				case 'verify':
					if (isset($_POST)) {
						$username = static::sanitize($_POST['username']);
						$password = static::sanitize($_POST['password']);

						$mapper = ServiceLocator::resolve('users');
						$user = $mapper->getUserByUsername($username);

						if ($user
								&& password_verify($password, $user->password)
								&& $user->isEnabled()) {
							$this->session->setUser($username);
							$this->session->pushNotification(
									"Signed in. Welcome back, {$user->username}!");
							$redir = 'index.php';
						} else {
							$this->session->pushNotification(
									'Wrong username or password. Please try again.');
							$redir = 'login.php';
						}
					}

					header("Location: $redir");
					break;

				case 'signup':
					$view = new \views\SignupView();
					$view->render();
					break;

				// Porta a termine la creazione di un nuovo account utente
				case 'save':
					if (isset($_POST)) {
						$mapper = ServiceLocator::resolve('users');

						$state['username'] = static::sanitize($_POST['username']);
						$state['password'] = static::sanitize($_POST['password']);
						$state['name'] = static::sanitize($_POST['name']);
						$state['address'] = static::sanitize($_POST['address']);
						$state['mail_pri'] = static::sanitize($_POST['mail_pri']);
						$state['mail_sec'] = static::sanitize($_POST['mail_sec']);

						try {
							$user = $mapper->create('user', $state);

						} catch (\models\InvalidDataException $e) {
							static::abort(
								'Couldn\'t complete operation. Invalid or missing data.',
								$e->getErrors()
							);

						} catch (\mysqli_sql_exception $e) {
							static::abort(
								'Username already taken. Please choose another one.',
								['username' => 'username already taken']
							);
						}

						$this->session->setUser($user->username);
						$this->session->pushNotification(
								"Account created. Welcome, {$user->username}!");
					}

					header('Location: index.php');
					break;

				// Porta a termine il reset della password utilizzando un link fornito da un admin
				case 'reset_password':
					$username = static::sanitize($_GET['id']);
					$password = static::sanitize($_GET['pw']);

					$mapper = ServiceLocator::resolve('users');
					$user = $mapper->getUserByUsername($username);

						if ($user && password_verify($password, $user->password)) {
							$prompt = "Welcome back, {$user->username}! Please set your new password.";

							// Effettua l'accesso per l'utente
							$this->session->setUser($user->username);

							// Chiede di impostare una nuova password
							if (!$this->session->holdsNotification())
								$this->session->pushNotification($prompt);

							// Visualizza il relativo form
							$view = new \views\PasswordChangeView($password);
							$view->render();

						} else {
							static::abort('Invalid password reset link.');
						}

					break;
			}
		}
	}

	new LoginController();
?>