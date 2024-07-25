<?php namespace views;
	
	class Movie extends AbstractView {
		private $movie;

		public function __construct($session, $movie) {
			parent::__construct($session);

			$this->movie = $movie;
		}

		public function renderCard() {

			echo <<<EOF
			<div class="card movie">
				<div class="poster" style="background-image: url('na.webp')"><span class="material-symbols-outlined"></span></div>
				<h1>{$this->movie->title}</h1>
				<div>{$this->movie->year}</div>
			</div>
			EOF;
		}

		public function render() {

			echo <<<EOF
			<div id="backdrop" style="background-image: url('na.webp')">
				<div class="blur">
					<div id="overview" class="wrapper">
						<div id="poster" class="poster" style="background-image: url('na.webp')">
							<span class="material-symbols-outlined"></span>
							<div class="flex status"><span class="material-symbols-outlined"></span>Pending approval</div>
						</div>
						<div id="description">
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
						</div>
					</div>
				</div>
			</div>
			EOF;
		}
	}
?>