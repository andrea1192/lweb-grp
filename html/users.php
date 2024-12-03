<?php namespace controllers;

	require_once('AbstractController.php');

	class UsersController extends AbstractController {

		public function route() {
			$user_id = static::sanitize($_GET['id'] ?? '');

			// Livello di privilegio richiesto per tutte le azioni: 2 (mod)
			if (!$this->session->isMod())
				header('Location: index.php');

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\UsersView();
					$view->render();
					break;

				case 'edit':
					$view = new \views\UserEditView($user_id);
					$view->render();
					break;

				case 'update':
					if (isset($_POST)) {
						$mapper = ServiceLocator::resolve('users');
						$user = $mapper->read($user_id);

						if ($user) {
							$user->name = static::sanitize($_POST['name']);
							$user->address = static::sanitize($_POST['address']);
							$user->mail_pri = static::sanitize($_POST['mail_pri']);
							$user->mail_sec = static::sanitize($_POST['mail_sec']);

							$mapper->update($user);
							$this->session->pushNotification(
									'Changes saved.');
						} else {
							static::abort(
									"User \"{$user->username}\" not found.");
						}
					}

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'ban':
					if (isset($_POST)) {
						$mapper = ServiceLocator::resolve('users');
						$user = $mapper->read($user_id);

						if ($user) {
							$user->reputation += $user::REPUTATION_DELTAS['ban'];
							$user->setPrivilege(0);
							$mapper->update($user);
							$this->session->pushNotification(
									"User \"{$user->username}\" banned.");
						} else {
							static::abort(
									"User \"{$user->username}\" not found.");
						}
					}

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'unban':
					if (isset($_POST)) {
						$mapper = ServiceLocator::resolve('users');
						$user = $mapper->read($user_id);

						if ($user) {
							$user->setPrivilege(1);
							$mapper->update($user);
							$this->session->pushNotification(
									"User \"{$user->username}\" unbanned.");
						} else {
							static::abort(
									"User \"{$user->username}\" not found.");
						}
					}

					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}
	}

	new UsersController();
?>