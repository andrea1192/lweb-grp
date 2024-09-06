<?php namespace views;
	
	class Movie extends AbstractView {
		protected const BACKDROPS_PATH = 'static/backdrops/';
		protected const POSTERS_PATH = 'static/posters/';
		protected const MEDIA_EXT = '.jpg';
		protected $movie;

		public function __construct($session, $movie) {
			parent::__construct($session);

			$this->movie = $movie;
		}

		public function generateURL($action = 'display') {

			switch ($action) {
				default:
				case 'display': return "movie.php?id={$this->movie->id}";
				case 'edit': return "movie.php?id={$this->movie->id}&action=edit";
				case 'save': return "movie.php?id={$this->movie->id}&action=save";
				case 'delete': return "movie.php?id={$this->movie->id}&action=delete";
			}
		}

		public function displayReference() {
			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();

			echo <<<EOF
			<div id="backdrop" {$backdrop}>
				<div class="blur">
					<div id="reference" class="header wrapper">
						{$poster}
						<div class="details">
							<h1>{$this->movie->title}</h1>
							<div>{$this->movie->year}, {$this->movie->duration}'</div>
						</div>
					</div>
				</div>
			</div>
			EOF;
		}

		public function displayCard() {
			$href = $this->generateURL();
			$poster = $this->generatePoster();

			echo <<<EOF
			<div class="card movie">
				<a href="{$href}"></a>
				{$poster}
				<h1>{$this->movie->title}</h1>
				<div>{$this->movie->year}</div>
			</div>
			EOF;
		}

		public function display() {
			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();
			$approve_buttons = $this->generateApproveButtons();

			echo <<<EOF
			<div id="backdrop" {$backdrop}>
				<div class="blur">
					<div id="overview" class="flex wrapper">
						{$poster}
						<div id="description" class="flex column">
							<h1>{$this->movie->title}</h1>
							<div>{$this->movie->year}, {$this->movie->duration}'</div>
							<p>{$this->movie->summary}</p>
							<div id="details">
								<div class="flex detail">
									<div>Director</div>
									<div>{$this->movie->director}</div>
								</div>
								<div class="flex detail">
									<div>Writer</div>
									<div>{$this->movie->writer}</div>
								</div>
							</div>
							{$approve_buttons}
						</div>
					</div>
				</div>
			</div>
			EOF;
		}

		public function edit() {
			$action = $this->generateURL('save');
			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();
			$save_buttons = $this->generateSaveButtons();

			$components = 'views\UIComponents';

			echo <<<EOF
			<div id="backdrop" {$backdrop}>
				<div class="blur">
					<form id="overview" class="flex wrapper" method="post" action="{$action}">
						{$poster}
						<div id="description" class="flex fields column">
							{$components::getHiddenInput('id', $this->movie->id)}
							{$components::getHiddenInput('status', $this->movie->status)}
							{$components::getFilledTextInput('Title', 'title', $this->movie->title)}
							<div class="flex fields" style="width: 30%">
								{$components::getFilledTextInput('Year', 'year', $this->movie->year)}
								{$components::getFilledTextInput('Duration', 'duration', $this->movie->duration)}
							</div>
							{$components::getFilledTextArea('Summary', 'summary', $this->movie->summary)}
							<div class="flex fields column" style="width: 40%">
								{$components::getFilledTextInput('Director', 'director', $this->movie->director)}
								{$components::getFilledTextInput('Writer', 'writer', $this->movie->writer)}
							</div>
							{$save_buttons}
						</div>
					</form>
				</div>
			</div>
			EOF;
		}

		protected function generateBackdrop() {
			$backdrop = static::BACKDROPS_PATH . $this->movie->id . static::MEDIA_EXT;

			return "style=\"background-image: url('{$backdrop}')\"";
		}

		protected function generatePoster() {
			$poster = static::POSTERS_PATH . $this->movie->id . static::MEDIA_EXT;
			$status = $this->generateStatus();

			if (file_exists($poster)) {
				$poster = "style=\"background-image: url('{$poster}')\"";
				$placeholder = '';
			} else {
				$poster = '';
				$placeholder = UIComponents::getIcon('movie', 'placeholder');
			}

			return <<<EOF
			<div id="poster" class="poster" {$poster}>
				{$status}
				{$placeholder}
			</div>
			EOF;
		}

		protected function generateStatus() {
			return '';
		}

		protected function generateApproveButtons() {
			return '';
		}

		protected function generateSaveButtons() {
			$left = '';
			$right = '';

			$right .= UIComponents::getOutlinedButton('Cancel', '');
			$right .= UIComponents::getFilledButton('Save changes', 'save');

			return <<<EOF
			<div class="flex bottom">
				<div class="flex left">
					{$left}
				</div>

				<div class="flex right">
					{$right}
				</div>
			</div>
			EOF;
		}
	}

	class Request extends Movie {

		protected function generateStatus() {
			$label = '';

			switch ($this->movie->status) {
				default:
				case 'submitted':
					$label = 'Pending approval';
					$icon = 'pending_actions';
					break;
				case 'accepted':
					$label = 'Accepted';
					$icon = 'thumb_up';
					break;
				case 'rejected':
					$label = 'Rejected';
					$icon = 'thumb_down';
					break;
			}

			return UIComponents::getOverlay($label, $icon, 'status');
		}

		protected function generateApproveButtons() {
			$left = '';
			$right = '';

			$components = '\views\UIComponents';

			if ($this->session->isMod()) {
				$left .= UIComponents::getTonalButton('Approve request', 'check', '#');
				$left .= UIComponents::getOutlinedButton('Decline request', 'close', '#');
			}

			if ($this->session->isAdmin()) {
				$right .= UIComponents::getOutlinedButton('', 'edit', $this->generateURL('edit'));
				$right .= UIComponents::getOutlinedButton('', 'delete', $this->generateURL('delete'));
			}

			return <<<EOF
			<div class="flex bottom">
				<div class="flex left">
					{$left}
				</div>

				<div class="flex right">
					{$right}
				</div>
			</div>
			EOF;
		}
	}
?>