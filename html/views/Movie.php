<?php namespace views;
	
	class Movie extends AbstractView {
		protected $movie;

		public function __construct($session, $movie) {
			parent::__construct($session);

			$this->movie = $movie;
		}

		private function generateURL() {

			return "movie.php?id={$this->movie->id}";
		}

		public function renderCard() {
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

		public function render() {
			$status = $this->generateStatus();
			$action_buttons = $this->generateActionButtons();

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
								<div class="flex align detail">
									<div>Director</div>
									<div>{$this->movie->director}</div>
								</div>
								<div class="flex align detail">
									<div>Writer</div>
									<div>{$this->movie->writer}</div>
								</div>
							</div>
							{$action_buttons}
						</div>
					</div>
				</div>
			</div>
			EOF;
		}

		protected function generateStatus() {
			return '';
		}

		protected function generateActionButtons() {
			return '';
		}
	}

	class Request extends Movie {

		protected function generateStatus() {
			$label = '';

			switch ($this->movie->status) {
				default:
				case 'submitted': $label = 'Pending approval'; break;
				case 'accepted': $label = 'Accepted'; break;
				case 'rejected': $label = 'Rejected'; break;
			}

			return <<< EOF
			<div class="flex align status {$this->movie->status}">
				<span class="material-symbols-outlined"></span>
				<span class="label">{$label}</span>
			</div>
			EOF;
		}

		protected function generateActionButtons() {
			$left = '';
			$right = '';

			if ($this->session->isMod()) {
				$left .= <<<EOF
				<button class="accept">
					<span class="material-symbols-outlined"></span><span class="label">Approve request</span>
				</button>
				<button class="reject">
					<span class="material-symbols-outlined"></span><span class="label">Decline request</span>
				</button>
				EOF;
			}

			if ($this->session->isAdmin()) {
				$right .= <<<EOF
				<button class="edit">
					<span class="material-symbols-outlined"></span>
				</button>
				<button class="delete">
					<span class="material-symbols-outlined"></span>
				</button>
				EOF;
			}

			return <<<EOF
			<div class="flex align bottom">
				<div class="flex align left">
					{$left}
				</div>

				<div class="flex align right">
					{$right}
				</div>
			</div>
			EOF;
		}
	}
?>