<?php namespace controllers;

	require_once('AbstractController.php');

	class SetupController extends AbstractController {

		public function route() {
			require_once('connection.php'); // costanti con i percorsi da utilizzare

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\SetupView();
					$view->render();
					break;

				case 'install':
					try {
						// NOTA: L'ordine di inizializzazione è significativo
						// 'users' va prima dei contenuti generati dagli utenti
						// 'posts' va prima delle diverse tipologie di post

						$sample_users = isset($_POST['setup_users']) ? BUILTIN_USERS : null;
						ServiceLocator::resolve('users')->init($sample_users);

						$sample_content = isset($_POST['setup_sample']) ? DIR_SAMPLE : null;
						ServiceLocator::resolve('requests')->init($sample_content);
						ServiceLocator::resolve('movies')->init($sample_content);
						ServiceLocator::resolve('posts')->init($sample_content);
						ServiceLocator::resolve('reviews')->init($sample_content);
						ServiceLocator::resolve('questions')->init($sample_content);
						ServiceLocator::resolve('answers')->init($sample_content);
						ServiceLocator::resolve('spoilers')->init($sample_content);
						ServiceLocator::resolve('extras')->init($sample_content);
						ServiceLocator::resolve('comments')->init($sample_content);
						ServiceLocator::resolve('likes')->init($sample_content);
						ServiceLocator::resolve('usefulnesses')->init($sample_content);
						ServiceLocator::resolve('agreements')->init($sample_content);
						ServiceLocator::resolve('spoilages')->init($sample_content);
						ServiceLocator::resolve('reactions')->init($sample_content);
						ServiceLocator::resolve('reports')->init($sample_content);

					} catch (\mysqli_sql_exception $e) {
						static::abort("Install failed. Database error: {$e->getMessage()}");

					} catch (\Exception $e) {
						static::abort("Install failed. {$e->getMessage()}");
					}

					// Inizializza le cartelle di poster (locandine) e backdrop (sfondi)
					if (isset($_POST['setup_sample'])) {
						$sample_bdrops = str_replace(DIR_STATIC, DIR_SAMPLE, DIR_BACKDROPS);
						$sample_posters = str_replace(DIR_STATIC, DIR_SAMPLE, DIR_POSTERS);

						static::copy_media($sample_bdrops, DIR_BACKDROPS);
						static::copy_media($sample_posters, DIR_POSTERS);

					} else {
						if (!is_dir(DIR_BACKDROPS))
							mkdir(DIR_BACKDROPS);
						if (!is_dir(DIR_POSTERS))
							mkdir(DIR_POSTERS);
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

					// Ripristina le cartelle di poster (locandine) e backdrop (sfondi)
					if (is_dir(DIR_BACKDROPS) || is_dir(DIR_POSTERS)) {
						static::remove_media(DIR_BACKDROPS);
						static::remove_media(DIR_POSTERS);
					}

					$this->session->pushNotification("Restore completed successfully.");
					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}

		/* Copia tutti i file presenti nella directory $src nella directory $dst */
		private static function copy_media($src, $dst) {

			if (!is_dir($src))
				return;

			if (!is_dir($dst))
				mkdir($dst);

			if ($files = scandir($src)) {
				$files = array_diff($files, ['.','..']);

				foreach ($files as $file) {
					$src_path = $src.$file;
					$dst_path = $dst.$file;

					if (!is_dir($src_path))
						copy($src_path, $dst_path);
				}
			}
		}

		/* Rimuove (unlink) tutti i file presenti nella directory $tgt */
		private static function remove_media($tgt) {

			if (!is_dir($tgt))
				return;

			if ($files = scandir($tgt)) {
				$files = array_diff($files, ['.','..']);

				foreach ($files as $file) {
					$path = $tgt.$file;

					if (!is_dir($path))
						unlink($path);
				}
			}
		}
	}

	new SetupController();
?>