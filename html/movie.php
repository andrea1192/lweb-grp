<?php namespace templates;

	require_once('views/MovieView.php');

	$view = new \views\MovieView('m1', 'question');

	// TODO: add XML prologue (xml version="1.0" encoding="UTF-8")
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Test</title>

		<link rel="stylesheet" href="../css/common.css" type="text/css" />
	</head>

	<body>
		<div id="header">Header</div>

		<?php $view->printOverview() ?>

		<div id="content" class="wrapper">
			<?php $view->printPosts() ?>

			<button class="fab new_post"><span class="material-symbols-outlined"></span><span class="label">New post</span></button>
		</div>

		<div id="footer"><div class="wrapper">Lorem ipsum dolor</div></div>
	</body>

</html>