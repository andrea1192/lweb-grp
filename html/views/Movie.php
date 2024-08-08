<?php namespace views;
	
	class Movie extends AbstractView {
		protected $movie;

		public function __construct($session, $movie) {
			parent::__construct($session);

			$this->movie = $movie;
		}

		protected function generateURL($action = 'display') {

			switch ($action) {
				default:
				case 'display': return "movie.php?id={$this->movie->id}";
				case 'edit': return "movie.php?id={$this->movie->id}&action=edit";
			}
		}

		public function displayCard() {
			$href = $this->generateURL();
			$status = $this->generateStatus();

			echo <<<EOF
			<div class="card movie">
				<a href="{$href}"></a>
				<div class="poster" style="background-image: url('na.webp')">
					<span class="material-symbols-outlined"></span>
					{$status}
				</div>
				<h1>{$this->movie->title}</h1>
				<div>{$this->movie->year}</div>
			</div>
			EOF;
		}

		public function display() {
			$status = $this->generateStatus();
			$approve_buttons = $this->generateApproveButtons();

			echo <<<EOF
			<div id="backdrop" style="background-image: url('na.webp')">
				<div class="blur">
					<div id="overview" class="flex wrapper">
						<div id="poster" class="poster" style="background-image: url('na.webp')">
							<span class="material-symbols-outlined"></span>
							{$status}
						</div>
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
			$status = $this->generateStatus();
			$save_buttons = $this->generateSaveButtons();

			echo <<<EOF
			<div id="backdrop" style="background-image: url('na.webp')">
				<div class="blur">
					<div id="overview" class="flex wrapper">
						<div id="poster" class="poster" style="background-image: url('na.webp')">
							<span class="material-symbols-outlined"></span>
							{$status}
						</div>
						<div id="description" class="flex column" style="gap: 25px">
							<label>
								<span class="label">Title</span>
								<input class="filled" name="title" type="text" value="{$this->movie->title}" />
							</label>
							<div class="flex" style="gap: 25px; width: 30%">
								<label>
									<span class="label">Year</span>
									<input class="filled" name="year" type="text" value="{$this->movie->year}" />
								</label>
								<label>
									<span class="label">Duration</span>
									<input class="filled" name="duration" type="text" value="{$this->movie->duration}" />
								</label>
							</div>

							<label>
								<span class="label">Summary</span>
								<textarea class="filled" rows="5" cols="80">{$this->movie->summary}</textarea>
							</label>

							<div class="flex column" style="gap: 25px; width: 40%">
								<label>
									<span class="label">Director</span>
									<input class="filled" name="director" type="text" value="{$this->movie->director}" />
								</label>
								<label>
									<span class="label">Writer</span>
									<input class="filled" name="writer" type="text" value="{$this->movie->writer}" />
								</label>
							</div>
							{$save_buttons}
						</div>
					</div>
				</div>
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

			$right .= UIComponents::getOutlinedButton('Cancel', '', '#');
			$right .= UIComponents::getFilledButton('Save changes', 'save', '#');

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
				$right .= UIComponents::getOutlinedButton('', 'delete', '#');
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