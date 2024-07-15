<?php namespace views;
	
	class Movie {

		public static function generateHTML($movie) {

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
			EOF;
		}
	}
?>