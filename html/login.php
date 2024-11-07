<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class LoginController extends AbstractController {

		public function route() {

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'signin':
					$view = new \views\SigninView($this->session);
					$view->render();
					break;

				case 'signout':
					$this->session->setUser(null);
					$this->session->pushNotification('Signed out. See you soon!');

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

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
					$view = new \views\SignupView($this->session);
					$view->render();
					break;

				case 'save':
					if (isset($_POST)) {
						$user = new \models\User();
						$mapper = ServiceLocator::resolve('users');

						$user->username = static::sanitize($_POST['username']);
						$user->password = static::sanitize($_POST['password']);
						$user->password = password_hash($user->password, PASSWORD_DEFAULT);

						$user->name = static::sanitize($_POST['name']);
						$user->address = static::sanitize($_POST['address']);
						$user->mail_pri = static::sanitize($_POST['mail_pri']);
						$user->mail_sec = static::sanitize($_POST['mail_sec']);

						try {
							$mapper->insert($user);
						} catch (\Exception $e) {
							static::abort('Username already taken. Please choose another one.');
						}

						$this->session->setUser($user->username);
						$this->session->pushNotification(
								"Account created. Welcome, {$user->username}!");
					}

					header('Location: index.php');
					break;
			}
		}
	}

	new LoginController();
?>