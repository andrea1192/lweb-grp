<?php namespace views;
	
	class Movie extends AbstractView {
		private $movie;
		private $tab;

		public function __construct($session, $movie, $tab) {
			parent::__construct($session);

			$this->movie = $movie;
			$this->tab = $tab;
		}

		public function generateCard() {

			return <<<EOF
			<div class="card movie">
				<div class="poster" style="background-image: url('na.webp')"><span class="material-symbols-outlined"></span></div>
				<h1>{$this->movie->title}</h1>
				<div>{$this->movie->year}</div>
			</div>
			EOF;
		}

		private function generateTabs() {
			$base_URL = $_SERVER['SCRIPT_NAME'];
			$list = '';

			$tabs = [
				'review' => 'Reviews',
				'question' => 'Q&amp;A',
				'spoiler' => 'Spoilers',
				'extra' => 'Extras'
			];

			foreach ($tabs as $tab => $label) {
				$query = [
					'id' => $this->movie->id,
					'tab' => $tab
				];

				$URL = $base_URL.'?'.http_build_query($query);

				$active = ($this->tab == $tab) ? 'class="active"' : '';

				$list .= "<li><a href=\"{$URL}\" {$active}>{$label}</a></li>";
			}

			return $list;
		}

		public function render() {

			$tabs = $this->generateTabs();

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

			<div id="tabs_bar">
				<ul id="tabs" class="wrapper">
					{$tabs}
				</ul>
			</div>
			EOF;
		}
	}
?>