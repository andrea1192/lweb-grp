<?php namespace controllers;

	require_once('AbstractController.php');

	class SetupController extends AbstractController {

		public function route() {
			require_once('connection.php');

			switch ($_REQUEST['action'] ?? '') {

				default:
				case 'display':
					$view = new \views\SetupView();
					$view->render();
					break;

				case 'install':
					try {
						$sample_content = isset($_POST['setup_sample']) ? DIR_SAMPLE : null;
						ServiceLocator::resolve('movies')->init($sample_content);
						ServiceLocator::resolve('requests')->init($sample_content);
						ServiceLocator::resolve('posts')->init($sample_content);
						ServiceLocator::resolve('comments')->init($sample_content);
						ServiceLocator::resolve('reactions')->init($sample_content);
						ServiceLocator::resolve('answers')->init($sample_content);
						ServiceLocator::resolve('reports')->init($sample_content);

						$sample_users = isset($_POST['setup_users']) ? BUILTIN_USERS : null;
						ServiceLocator::resolve('users')->init($sample_users);

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

					$this->session->pushNotification("Install completed successfully.");
					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;

				case 'restore':
					try {
						ServiceLocator::resolve('movies')->restore();
						ServiceLocator::resolve('requests')->restore();
						ServiceLocator::resolve('posts')->restore();
						ServiceLocator::resolve('comments')->restore();
						ServiceLocator::resolve('reactions')->restore();
						ServiceLocator::resolve('answers')->restore();
						ServiceLocator::resolve('reports')->restore();

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