<?php namespace views;
	
	class Post {

		public static function generateReactionButtons($reaction_types) {
			$html = '';

			if (!$reaction_types) return $html;

			foreach ($reaction_types as $type => $stats) {

				switch ($type) {
					case 'like':
						$html .= <<<EOF
						<button class="text likes">
							<span class="material-symbols-outlined"></span><span class="label">{$stats->count_up}</span>
						</button>
						<button class="text dislikes">
							<span class="material-symbols-outlined"></span><span class="label">{$stats->count_down}</span>
						</button>
						EOF;
						break;

					case 'usefulness':
						$html .= <<<EOF
						<button class="text usefulness" disabled="disabled">
							<div class="tooltip">
								<span class="material-symbols-outlined"></span>Useful?
								<span class="rate">
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
								</span>
							</div>
							<span class="material-symbols-outlined"></span><span class="label">{$stats->average}</span>
						</button>
						EOF;
						break;

					case 'agreement':
						$html .= <<<EOF
						<button class="text agreement" disabled="disabled">
							<div class="tooltip">
								<span class="material-symbols-outlined"></span>Useful?
								<span class="rate">
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
									<span class="material-symbols-outlined">star</span>
								</span>
							</div>
							<span class="material-symbols-outlined"></span><span class="label">{$stats->average}</span>
						</button>
						EOF;
						break;

					case 'spoilage':
						$html .= <<<EOF
						<button class="text spoil_level" disabled="disabled">
							<div class="tooltip">
								<span class="material-symbols-outlined"></span>Spoiler level:
								<span class="rate">
									<select name="rating">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
									</select>
								</span>
							</div>
							<span class="material-symbols-outlined"></span><span class="label">{$stats->average}</span>
						</button>
						EOF;
						break;

					default:
						$html .= '<div>Unknown reaction type</div>';
						break;
				}
			}

			return $html;
		}

		public static function generateHTML($post) {

			$rating = (in_array('models\RatedPost', class_parents($post))) ? <<<EOF
			<div class="rating">
				<span class="centered">{$post->rating}</span>
			</div>
			EOF : '';

			$reaction_buttons = self::generateReactionButtons($post->reactions);

			$action_buttons = ('models\Question' == get_class($post)) ? <<<EOF
			<button class="answer_compose">
				<span class="material-symbols-outlined"></span><span class="label">Answer</span>
			</button>
			EOF : '';

			return <<<EOF
			<div class="card post">
				<div class="flex header">
					{$rating}
					<div class="details">
						<h1>{$post->title}</h1>
						<div class="flex published">
							<span class="author">{$post->author}</span>
							<span class="date">{$post->date}</span>
						</div>
					</div>
					<button class="right text kebab">
						<span class="material-symbols-outlined"></span>
						<div class="dropdown">
							<ul>
								<li class="flex edit"><span class="material-symbols-outlined"></span><span class="label">Edit</span></li>
								<li class="flex delete"><span class="material-symbols-outlined"></span><span class="label">Delete</span></li>
								<li class="flex report"><span class="material-symbols-outlined"></span><span class="label">Report</span></li>
							</ul>
						</div>
					</button>
				</div>
				<div class="content">
					<p>{$post->text}</p>
				</div>
				<div class="flex footer">
					<div class="flex left">
						{$reaction_buttons}
					</div>
					<div class="flex right">
						{$action_buttons}
					</div>
				</div>
			</div>
			EOF;
		}
	}
?>