<?php namespace views;

	\ob_start('\views\AbstractView::validateHTML');

	static::sendHeaders();
	static::printPrologue();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title><?php $this->printTitle() ?></title>

		<link rel="stylesheet" href="css/common.css" type="text/css" />
	</head>

	<body>
		<?php $this->printHeader() ?>

		<div id="content" class="pane">
			<?php $this->printGrid() ?>

		</div>

		<div>
			<?php $this->printActionButton() ?>
		</div>

		<?php $this->printFooter() ?>
	</body>

</html>

<?php \ob_end_flush() ?>