<?php namespace controllers;

	require_once('AbstractController.php');

	class SetupController extends AbstractController {

		public function route() {
			require_once('connection.php'); // costanti con le credenziali per il database

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\SetupView();
					$view->render();
					break;

				case 'install':
					try {
						// Inizializza le tabelle

						// NOTA: L'ordine di inizializzazione è significativo
						// 'users' va prima dei contenuti generati dagli utenti
						// 'posts' va prima delle sue specializzazioni, e 'reactions' dopo
						ServiceLocator::resolve('users')->init();

						ServiceLocator::resolve('requests')->init();
						ServiceLocator::resolve('movies')->init();
						ServiceLocator::resolve('posts')->init();
						ServiceLocator::resolve('reviews')->init();
						ServiceLocator::resolve('qa')->init();
						ServiceLocator::resolve('questions')->init();
						ServiceLocator::resolve('answers')->init();
						ServiceLocator::resolve('spoilers')->init();
						ServiceLocator::resolve('extras')->init();
						ServiceLocator::resolve('comments')->init();

						ServiceLocator::resolve('likes')->init();
						ServiceLocator::resolve('usefulnesses')->init();
						ServiceLocator::resolve('agreements')->init();
						ServiceLocator::resolve('spoilages')->init();
						ServiceLocator::resolve('reactions')->init();
						ServiceLocator::resolve('reports')->init();

						// A seconda delle scelte operate nel form di installazione, carica utenti
						// e/o contenuti di esempio definiti in connection.php

						// Utenti di esempio
						if (isset($_POST['setup_users'])) {
							$data = BUILTIN_USERS;

							ServiceLocator::resolve('users')->load($data);
						}

						// Contenuti di esempio
						if (isset($_POST['setup_sample'])) {
							$data = SAMPLE_CONTENT;

							foreach ($data as $repo => $content)
								ServiceLocator::resolve($repo)->load($content);
						}

					} catch (\mysqli_sql_exception $e) {
						static::abort("Install failed. Database error: {$e->getMessage()}");

					} catch (\Exception $e) {
						static::abort("Install failed. {$e->getMessage()}");
					}

					$message = "Install completed successfully.";
					$message .= " <a href=\"index.php\">Go to site &gt;&gt;&gt;</a>";

					$this->session->pushNotification($message);
					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'restore':
					try {
						// NOTA: L'ordine di ripristino è significativo
						ServiceLocator::resolve('likes')->restore();
						ServiceLocator::resolve('usefulnesses')->restore();
						ServiceLocator::resolve('agreements')->restore();
						ServiceLocator::resolve('spoilages')->restore();
						ServiceLocator::resolve('reports')->restore();

						// restore() su 'posts' ripristina a cascata le specializzazioni di 'post'
						ServiceLocator::resolve('posts')->restore();
						ServiceLocator::resolve('requests')->restore();

						// Chiude eventuali sessioni aperte prima di ripristinare gli utenti
						ServiceLocator::resolve('session')->setUser(null);
						ServiceLocator::resolve('users')->restore();

					} catch (\mysqli_sql_exception $e) {
						static::abort("Restore failed. Database error: {$e->getMessage()}");

					} catch (\Exception $e) {
						static::abort("Restore failed. {$e->getMessage()}");
					}

					$this->session->pushNotification("Restore completed successfully.");
					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}
	}

	new SetupController();
?>