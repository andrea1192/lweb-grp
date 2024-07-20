<?php namespace views;
	
	class Movie {

		public static function generateCard($movie) {

			return <<<EOF
			<div class="card movie">
				<div class="poster" style="background-image: url('na.webp')"><span class="material-symbols-outlined"></span></div>
				<h1>{$movie->title}</h1>
				<div>{$movie->year}</div>
			</div>
			EOF;
		}

		public static function generateTabs($movie, $current_tab) {
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
					'id' => $movie,
					'tab' => $tab
				];

				$URL = $base_URL.'?'.http_build_query($query);

				$active = ($current_tab == $tab) ? 'class="active"' : '';

				$list .= "<li><a href=\"{$URL}\" {$active}>{$label}</a></li>";
			}

			return $list;
		}

		public static function generateHTML($movie, $tab) {

			$tabs = self::generateTabs($movie->id, $tab);

			return <<<EOF
			<div id="backdrop" style="background-image: url('na.webp')">
				<div class="blur">
					<div id="overview" class="wrapper">
						<div id="poster" class="poster" style="background-image: url('na.webp')">
							<span class="material-symbols-outlined"></span>
							<div class="flex status"><span class="material-symbols-outlined"></span>Pending approval</div>
						</div>
						<div id="description">
							<h1>{$movie->title}</h1>
							<div>{$movie->year}, {$movie->duration}'</div>

							<p>{$movie->summary}</p>

							<div id="details">
								<div class="flex detail">
									<div>Director</div>
									<div>{$movie->director}</div>
								</div>
								<div class="flex detail">
									<div>Writer</div>
									<div>{$movie->writer}</div>
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