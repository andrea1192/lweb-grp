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

				// Porta a termine la modifica dei dati di un utente da parte di un amministratore
				// Per i dati dell'utente corrente, l'azione è *save* in profile.php
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

				// Porta a termine il ban di un utente (modificando il suo livello di privilegio)
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

				// Porta a termine l'unban di un utente (modificando il suo livello di privilegio)
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