<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	class ProfileController extends AbstractController {

		public function route() {

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					$view = new \views\ProfileView($this->session);
					$view->render();
					break;

				case 'save':
					// TODO: Aggiungi controlli privilegi con ev. redirect
					if (isset($_POST)) {
						$password = static::sanitize($_POST['password']);

						$user = $this->session->getUser();
						$mapper = ServiceLocator::resolve('users');

						if ($user && password_verify($password, $user->password)) {
							$user->name = static::sanitize($_POST['name']);
							$user->address = static::sanitize($_POST['address']);
							$user->mail_pri = static::sanitize($_POST['mail_pri']);
							$user->mail_sec = static::sanitize($_POST['mail_sec']);

							$mapper->update($this->session->getUsername(), $user);
						}
					}

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}
	}

	new ProfileController();
?>