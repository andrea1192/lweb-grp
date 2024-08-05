<?php /* TODO: add XML prologue (xml version="1.0" encoding="UTF-8") */ ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title><?php $this->printTitle() ?></title>

		<link rel="stylesheet" href="../css/common.css" type="text/css" />
	</head>

	<body>
		<?php $this->printHeader() ?>

		<div id="content" class="pane">
			<?php $this->printList() ?>

		</div>

		<button class="fab new_post"><span class="material-symbols-outlined"></span><span class="label">Add movie</span></button>

		<?php $this->printFooter() ?>
	</body>

</html>