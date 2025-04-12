<?php namespace views;
	
	/* Visualizzazione di un oggetto di tipo \models\Movie */
	class Movie extends AbstractView {
		protected $movie;
		protected $errors;

		public function __construct($movie) {
			parent::__construct();

			$this->movie = $movie;

			if (str_ends_with($_SERVER['SCRIPT_NAME'], '/movie.php') && $this->session->holdsErrors())
				$this->errors = $this->session->popErrors();
		}

		/* Genera un URL per l'azione richiesta, se ammessa sull'oggetto di riferimento */
		public function generateURL($action = 'display') {
			$URL = 'movie.php';

			if ($this->movie) {
				$type = ($this->movie::class != 'models\Movie') ? 'request' : 'movie';
				$URL .= "?type={$type}&id={$this->movie->id}";
			}

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

		/* Visualizza una selezione dei dettagli dell'oggetto di riferimento. Questa vista è
		* utilizzata dalla pagina di dettaglio di un post per mostrare a quale film è riferito.
		*/
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

		/* Visualizza una selezione dei dettagli dell'oggetto di riferimento. Questa vista è
		* utilizzata dalla griglia dei film in archivio per mostrare locandina e titolo dei film.
		*/
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

		/* Visualizza tutti i dettagli disponibili per l'oggetto di riferimento. Questa vista è
		* utilizzata dalla relativa pagina di dettaglio.
		*/
		public function display() {
			$placeholder = 'N/A';

			$backdrop = $this->generateBackdrop();
			$poster = $this->generatePoster();
			$action_buttons = $this->generateActionButtons();

			// NOTA: \views\Request non fa override di questo metodo
			// Se necessario, rimpiazza con un placeholder dettagli opzionali per questa vista
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

			// Rimpiazza newline con <br /> per preservare lo spazio nella visualizzazione
			$movie_summary = nl2br($movie_summary);

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

		/* Form di modifica dei dettagli */
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

		/* Form di primo inserimento dei dettagli */
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

		/* Genera codice per visualizzare lo sfondo, se presente */
		protected function generateBackdrop() {

			if (!empty($this->movie->backdrop)) {
				$backdrop = $this->movie->backdrop;
				$backdrop_b64 = base64_encode($backdrop);
				$type = getimagesizefromstring($backdrop)['mime'];

				return "style=\"background-image: url('data:{$type};base64,{$backdrop_b64}')\"";
			} else {
				return '';
			}
		}

		/* Genera codice per visualizzare la locandina, se presente */
		protected function generatePoster() {

			if (!empty($this->movie->poster)) {
				$poster = $this->movie->poster;
				$poster_b64 = base64_encode($poster);
				$type = getimagesizefromstring($poster)['mime'];

				$poster = "style=\"background-image: url('data:{$type};base64,{$poster_b64}')\"";
				$placeholder = '';
			} else {

				if (!str_ends_with($_SERVER['SCRIPT_NAME'], '/post.php'))
					$cls = 'placeholder md-72 md-xlight';
				else
					$cls = 'placeholder md-24 md-light';

				$poster = '';
				$placeholder = UIComponents::getIcon('movie', cls: $cls);
			}

			return <<<EOF
			<div class="poster" {$poster}>
				{$this->generateStatus()}
				{$placeholder}
			</div>
			EOF;
		}

		/* [Non applicabile per \models\Movie] */
		protected function generateStatus() {
			return '';
		}

		/* Genera il pulsante di modifica */
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

		/* Genera i pulsanti per il caricamento di locandine e sfondi */
		protected function generateFiles() {
			$accept = implode(', ', array_keys($this->getMapper('movies')::MEDIA_TYPES));

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
				<div class="small">Accepted file types: {$accept}</div>
			</div>
			EOF;
		}

		/* Genera campi specifici dell'oggetto di riferimento. Utilizzato da edit() e compose():
		* facendo override di questo metodo, è possibile inserirvi campi applicabili a sottoclassi
		* di \models\Movie senza duplicare il codice di questi due metodi.
		*/
		protected function generateSpecialFields() {
			return UIComponents::getHiddenInput('type', 'movie');
		}

		/* Genera il pulsante di salvataggio */
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

	/* Visualizzazione di un oggetto di tipo \models\Request */
	class Request extends Movie {

		/* Genera codice per visualizzare lo status della richiesta */
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

			if (str_ends_with($_SERVER['SCRIPT_NAME'], '/post.php'))
				$label = '';

			return UIComponents::getOverlay($label, $icon, cls: 'status');
		}

		/* Genera campi specifici dell'oggetto di riferimento. Override per \models\Request. */
		protected function generateSpecialFields() {
			$fields = UIComponents::getHiddenInput('type', 'request');

			if ($this->movie) {
				$fields .= UIComponents::getHiddenInput('status', $this->movie->status ?? '');
				$fields .= UIComponents::getHiddenInput('author', $this->movie->author ?? '');
			}

			return $fields;
		}

		/* Genera i pulsanti di modifica/cancellazione e di revisione della richiesta */
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

		/* Genera il pulsante di salvataggio e quelli per accettare/respingere la richiesta */
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