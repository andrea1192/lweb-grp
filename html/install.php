<?php namespace controllers;

	require_once('AbstractController.php');
	require_once('views/DialogViews.php');

	require_once('models/Movies.php');
	require_once('models/Posts.php');
	require_once('models/Reactions.php');


	class SetupController extends AbstractController {

		public function route() {
			require_once('connection.php');
			$message = '';

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

					$message = "Install completed successfully.";

					$this->session->pushNotification($message);
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

					$message = "Restore completed successfully.";

					$this->session->pushNotification($message);
					header("Location: {$_SERVER['HTTP_REFERER']}");
					break;
			}
		}
	}

	new SetupController();
?>