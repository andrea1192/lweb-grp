<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class LoginController extends AbstractController {

		public function route() {
			$action = $_GET['action'] ?? '';

			switch ($action) {

				default:
				case 'signin':
					$view = new \views\SigninView($this->session);
					$view->render();
					break;

				case 'verify':
					if (isset($_POST)) {
						$username = static::sanitize($_POST['username']);
						$password = static::sanitize($_POST['password']);

						$mapper = ServiceLocator::resolve('users');
						$user = $mapper->getUserByUsername($username);

						if (password_verify($password, $user->password)) {
							$redir = 'index.php';
						} else {
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
					// TODO: Aggiungi controlli privilegi con ev. redirect
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

						$mapper->insert($user);
					}

					header('Location: index.php');
					break;
			}
		}
	}

	new LoginController();
?>