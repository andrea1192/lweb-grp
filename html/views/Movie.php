<?php namespace views;
	
	class Movie extends AbstractView {
		protected $movie;
		protected $errors;

		public function __construct($movie) {
			parent::__construct();

			$this->movie = $movie;

			if ($_SERVER['SCRIPT_NAME'] == '/movie.php' && $this->session->holdsErrors())
				$this->errors = $this->session->popErrors();
		}

		public function generateURL($action = 'display') {
			$URL = 'movie.php';

			if ($this->movie)
				$URL .= "?id={$this->movie->id}";

			switch ($action) {
				default:
				case 'display':
					break;
				case 'edit':
				case 'update':
				case 'accept':
				case 'reject':
				case 'delete':
					$URL .= "&action={$action}";
					break;
			}

			return htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
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
			$placeholder = 'N/A';

			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();
			$action_buttons = $this->generateActionButtons();

			$movie_title =
					(!empty($this->movie->title)) ? $this->movie->title : $placeholder;
			$movie_year =
					(!empty($this->movie->year)) ? $this->movie->year : $placeholder;
			$movie_duration =
					(!empty($this->movie->duration)) ? $this->movie->duration : $placeholder;
			$movie_summary =
					(!empty($this->movie->summary)) ? $this->movie->summary : $placeholder;
			$movie_director =
					(!empty($this->movie->director)) ? $this->movie->director : $placeholder;
			$movie_writer =
					(!empty($this->movie->writer)) ? $this->movie->writer : $placeholder;

			echo <<<EOF
			<div id="backdrop" {$backdrop}>
				<div class="blur">
					<div id="overview" class="flex wrapper">
						{$poster}
						<div id="description" class="flex">
							<h1>{$movie_title}</h1>
							<div>{$movie_year}, {$movie_duration}'</div>
							<p>{$movie_summary}</p>
							<div id="details">
								<div class="flex detail">
									<div>Director</div>
									<div>{$movie_director}</div>
								</div>
								<div class="flex detail">
									<div>Writer</div>
									<div>{$movie_writer}</div>
								</div>
							</div>
							{$action_buttons}
						</div>
					</div>
				</div>
			</div>
			EOF;
		}

		public function edit() {
			$action = $this->generateURL();
			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();
			$files = $this->generateFiles();
			$special_fields = $this->generateSpecialFields();
			$save_buttons = $this->generateSaveButtons();

			$components = 'views\UIComponents';
			$errors = $this->errors;

			echo <<<EOF
			<div id="backdrop" {$backdrop}>
				<div class="blur">
					<form id="overview" class="flex wrapper" method="post" action="{$action}" enctype="multipart/form-data">
						<div class="flex column">
							{$poster}
							{$files}
						</div>
						<div id="description" class="flex fields">
							{$components::getHiddenInput('id', $this->movie->id)}
							{$special_fields}
							{$components::getFilledTextInput(
									'Title',
									'title',
									$this->movie->title,
									errors: $errors
							)}
							<div class="flex fields" style="width: 40%">
								{$components::getFilledTextInput(
										'Year',
										'year',
										$this->movie->year,
										errors: $errors
								)}
								{$components::getFilledTextInput(
										'Duration',
										'duration',
										$this->movie->duration,
										errors: $errors
								)}
							</div>
							{$components::getFilledTextArea(
									'Summary',
									'summary',
									$this->movie->summary,
									errors: $errors
							)}
							<div class="flex fields column" style="width: 40%">
								{$components::getFilledTextInput(
										'Director',
										'director',
										$this->movie->director,
										errors: $errors
								)}
								{$components::getFilledTextInput(
										'Writer',
										'writer',
										$this->movie->writer,
										errors: $errors
								)}
							</div>
							{$save_buttons}
						</div>
					</form>
				</div>
			</div>
			EOF;
		}

		public function compose() {
			$action = $this->generateURL();
			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();
			$files = $this->generateFiles();
			$special_fields = $this->generateSpecialFields();
			$save_buttons = $this->generateSaveButtons();

			$components = 'views\UIComponents';
			$errors = $this->errors;

			echo <<<EOF
			<div id="backdrop" {$backdrop}>
				<div class="blur">
					<form id="overview" class="flex wrapper" method="post" action="{$action}" enctype="multipart/form-data">
						<div class="flex column">
							{$poster}
							{$files}
						</div>
						<div id="description" class="flex fields">
							{$special_fields}
							{$components::getFilledTextInput(
									'Title',
									'title',
									errors: $errors
							)}
							<div class="flex fields" style="width: 40%">
								{$components::getFilledTextInput(
										'Year',
										'year',
										errors: $errors
								)}
								{$components::getFilledTextInput(
										'Duration',
										'duration',
										errors: $errors
								)}
							</div>
							{$components::getFilledTextArea(
									'Summary',
									'summary',
									errors: $errors
							)}
							<div class="flex fields column" style="width: 40%">
								{$components::getFilledTextInput(
										'Director',
										'director',
										errors: $errors
								)}
								{$components::getFilledTextInput(
										'Writer',
										'writer',
										errors: $errors
								)}
							</div>
							{$save_buttons}
						</div>
					</form>
				</div>
			</div>
			EOF;
		}

		protected function generateBackdrop() {
			$dir = $this->getMapper('movies')::BACKDROPS_PATH;
			$ext = $this->getMapper('movies')::MEDIA_EXT;
			$id = $this->movie->id ?? '';

			$backdrop = $dir.$id.$ext;

			return "style=\"background-image: url('{$backdrop}')\"";
		}

		protected function generatePoster() {
			$dir = $this->getMapper('movies')::POSTERS_PATH;
			$ext = $this->getMapper('movies')::MEDIA_EXT;
			$id = $this->movie->id ?? '';

			$poster = $dir.$id.$ext;
			$status = $this->generateStatus();

			if (file_exists($poster)) {
				$poster = "style=\"background-image: url('{$poster}')\"";
				$placeholder = '';
			} else {

				if ($_SERVER['SCRIPT_NAME'] != '/post.php')
					$cls = 'placeholder md-72 md-xlight';
				else
					$cls = 'placeholder md-24 md-light';

				$poster = '';
				$placeholder = UIComponents::getIcon('movie', cls: $cls);
			}

			return <<<EOF
			<div class="poster" {$poster}>
				{$status}
				{$placeholder}
			</div>
			EOF;
		}

		protected function generateStatus() {
			return '';
		}

		protected function generateActionButtons() {
			$left = '';
			$right = '';

			if (!property_exists($this->movie, 'status')
					|| ($this->movie->status == 'submitted')) {

				if ($this->session->isAdmin()) {
					$right .= UIComponents::getOutlinedButton(
							'',
							'edit',
							$this->generateURL('edit'),
							content: UIComponents::getTooltip('Edit this data')
					);
				}
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

		protected function generateFiles() {
			$accept = $this->getMapper('movies')::MEDIA_TYPE;

			return <<<EOF
			<div class="flex column files">
				<label>
					<span class="label">Poster</span>
					<input class="filled" type="file" accept="{$accept}" name="poster" />
				</label>
				<label>
					<span class="label">Backdrop</span>
					<input class="filled" type="file" accept="{$accept}" name="backdrop" />
				</label>
				<div class="small">Accepted file types: image/jpeg</div>
			</div>
			EOF;
		}

		protected function generateSpecialFields() {
			return UIComponents::getHiddenInput('type', 'movie');
		}

		protected function generateSaveButtons() {
			$left = '';
			$right = '';

			$right .= UIComponents::getTonalButton('Save changes', 'save', action: 'update');

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

			if (!$this->movie)
				return '';

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
				case 'deleted':
					$label = 'Deleted';
					$icon = 'delete';
					break;
			}

			if ($_SERVER['SCRIPT_NAME'] == '/post.php')
				$label = '';

			return UIComponents::getOverlay($label, $icon, cls: 'status');
		}

		protected function generateSpecialFields() {
			$fields = UIComponents::getHiddenInput('type', 'request');

			if ($this->movie) {
				$fields .= UIComponents::getHiddenInput('status', $this->movie->status ?? '');
				$fields .= UIComponents::getHiddenInput('author', $this->movie->author ?? '');
			}

			return $fields;
		}

		protected function generateActionButtons() {
			$left = '';
			$right = '';

			if (!property_exists($this->movie, 'status')
					|| ($this->movie->status == 'submitted')) {

				if ($this->session->isMod()) {
					$left .= UIComponents::getTonalButton(
							'Review this content',
							'edit',
							$this->generateURL('edit'),
							content: UIComponents::getTooltip(
									'Edit this request and choose whether to accept or reject it')
					);

					$right .= UIComponents::getOutlinedButton(
							'',
							'edit',
							$this->generateURL('edit'),
							content: UIComponents::getTooltip('Edit this request')
					);
					$right .= UIComponents::getOutlinedButton(
							'',
							'delete',
							$this->generateURL('delete'),
							content: UIComponents::getTooltip('Delete this request')
					);
				}
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

		protected function generateSaveButtons() {
			$left = '';
			$right = '';

			if (!$this->movie) {
				$right .= UIComponents::getTonalButton(
						'Submit request',
						'send',
						action: 'create',
						content: UIComponents::getTooltip(
								'Submit this request to the staff for approval')
				);

			} elseif ($this->movie->status == 'submitted' && $this->session->isMod()) {
				$left .= UIComponents::getTonalButton(
						'Save and accept request',
						'check',
						action: 'accept',
						content: UIComponents::getTooltip(
								'Save changes and accept this request for inclusion in Movies')
				);
				$left .= UIComponents::getOutlinedButton(
						'Reject request',
						'close',
						action: 'reject',
						content: UIComponents::getTooltip(
								'Reject this request')
				);
				$right .= UIComponents::getOutlinedButton(
						'Save only',
						'save',
						action: 'update',
						content: UIComponents::getTooltip(
								'Save changes without accepting the request')
				);
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