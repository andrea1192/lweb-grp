<?php namespace views;
	
	/* Visualizzazione di un oggetto di tipo \models\Post */
	class Post extends AbstractView {
		protected const POST_TYPE = '';
		protected $post;
		protected $ref;
		protected $errors;

		protected static function getPostType() {
			return static::POST_TYPE;
		}

		public function __construct($post = null, $ref = null) {
			parent::__construct();

			$this->post = $post;
			$this->ref = $ref
					?? $post->movie
					?? $post->request;

			if ($_SERVER['SCRIPT_NAME'] == '/post.php' && $this->session->holdsErrors())
				$this->errors = $this->session->popErrors();
		}

		/* Genera un URL per l'azione richiesta, se ammessa sull'oggetto di riferimento */
		public function generateURL($action = 'display') {
			$URL = "post.php?type={$this->getPostType()}";

			if ($this->post)
				$URL .= "&id={$this->post->id}";

			switch ($action) {
				default:
				case 'display':
					break;
				case 'edit':
				case 'create':
				case 'update':
				case 'answer':
				case 'report':
				case 'delete':
				case 'elevate':
					$URL .= "&action={$action}";
					break;
			}

			return htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}

		/* Visualizza l'oggetto di riferimento, opzionalmente cambiandone una o più parti.
		*
		*	$active determina se visualizzare o meno i pulsanti di inserimento delle reazioni
		*	$content permette di cambiare il contenuto del post
		*	$reactions permette di cambiare le reazioni al post
		*/
		public function displayReference($active = true, $content = '', $reactions = '') {
			$rating = $this->generateRating();

			$deleted_icon = UIComponents::getIcon('delete', cls: 'translate');
			$status = ($this->post->status == 'active') ? '' : $deleted_icon;

			if ($active) {
				$dropdown_menu = $this->generateDropdownMenu();
				$reaction_buttons = Reaction::generateReactionButtons($this->post->reactions);
				$action_buttons = $this->generateActionButtons();
			} else {
				$dropdown_menu = '';
				$reaction_buttons = '';
				$action_buttons = '';
			}

			if (empty($content))
				$content = $this->post->text;

			echo <<<EOF
			<div class="post">
				<div class="header">
					{$rating}
					<div class="details">
						<h1>{$this->post->title}{$status}</h1>
						<div class="flex small">
							<span class="author">{$this->post->author}</span>
							<span class="date">{$this->post->date}</span>
						</div>
					</div>
					{$dropdown_menu}
				</div>
				<div class="flex content">
					{$content}
				</div>
				<div class="flex footer">
					<div class="flex left">
						{$reaction_buttons}
					</div>
					<div class="flex right">
						{$action_buttons}
					</div>
				</div>
				{$reactions}
			</div>
			EOF;
		}

		/* Visualizza l'oggetto di riferimento, senza cambiamenti, mediante displayReference() */
		public function display() {
			static::displayReference();
		}

		/* Form di modifica del post */
		public function edit() {
			$action = $this->generateURL('update');
			$reference_field = $this->generateReferenceField();
			$special_fields = $this->generateSpecialFields();
			$save_buttons = $this->generateSaveButtons();

			$components = 'views\UIComponents';
			$errors = $this->errors;

			echo <<<EOF
			<form class="post" method="post" action="{$action}">
				<div class="flex column">
					{$components::getHiddenInput(
							'type',
							$this->getPostType()
					)}
					{$components::getHiddenInput(
							'id',
							$this->post->id
					)}
					{$components::getHiddenInput(
							'status',
							$this->post->status
					)}
					{$reference_field}
					{$components::getHiddenInput(
							'author',
							$this->post->author
					)}
					{$components::getHiddenInput(
							'date',
							$this->post->date
					)}
					{$components::getTextInput(
							'Title',
							'title',
							$this->post->title,
							errors: $errors
					)}
					{$special_fields}
					{$components::getTextArea(
							'Text',
							'text',
							$this->post->text,
							errors: $errors
					)}
					<div class="flex footer">
						<div class="flex left">
							{$save_buttons}
						</div>

						<div class="flex right">
						</div>
					</div>
				</div>
			</form>
			EOF;
		}

		/* Form di primo inserimento del post */
		public function compose() {
			$action = $this->generateURL('create');
			$reference_field = $this->generateReferenceField();
			$special_fields = $this->generateSpecialFields();
			$save_buttons = $this->generateSaveButtons();

			$components = 'views\UIComponents';
			$errors = $this->errors;

			echo <<<EOF
			<form class="post" method="post" action="{$action}">
				<div class="flex column">
					{$components::getHiddenInput('type', $this->getPostType())}
					{$reference_field}
					{$components::getTextInput('Title', 'title', errors: $errors)}
					{$special_fields}
					{$components::getTextArea('Text', 'text', errors: $errors)}
					<div class="flex footer">
						<div class="flex left">
							{$save_buttons}
						</div>

						<div class="flex right">
						</div>
					</div>
				</div>
			</form>
			EOF;
		}

		/* [Non applicabile per classe base \models\Post] */
		protected function generateRating() {
			return '';
		}

		/* Genera il menu con i pulsanti per modificare/cancellare/segnalare il post */
		protected function generateDropdownMenu() {
			$items = '';

			if (!$this->session->isAllowed())
				return '';

			if ($this->session->isAuthor($this->post) || $this->session->isAdmin()) {
				$items .= UIComponents::getDropdownItem(
					'Edit',
					'edit',
					$this->generateURL('edit')
				);
			}

			if ($this->session->isAuthor($this->post) || $this->session->isMod()) {
				$items .= UIComponents::getDropdownItem(
					'Delete',
					'delete',
					$this->generateURL('delete')
				);
			}

			if (!$this->session->isAuthor($this->post)) {
				$items .= UIComponents::getDropdownItem(
					'Report',
					'report',
					$this->generateURL('report')
				);
			}

			if ($items == '')
				return '';
			else
				$dropdown = UIComponents::getDropdownMenu($items);

			return UIComponents::getOverflowMenu($dropdown);
		}

		/* Genera un campo nascosto con il film di riferimento. Utilizzato da edit() e compose();
		* fa in modo che i form di inserimento e modifica contengano tutti i dati di cui il
		* controller ha bisogno per portare a termine l'operazione.
		*/
		protected function generateReferenceField() {
			return UIComponents::getHiddenInput('movie', $this->ref);
		}

		/* Genera campi specifici dell'oggetto di riferimento. Utilizzato da edit() e compose();
		* facendo override di questo metodo, è possibile inserirvi campi applicabili a sottoclassi
		* di \models\Post senza duplicare il codice di questi due metodi.
		*/
		protected function generateSpecialFields() {
			return '';
		}

		/* Genera il pulsante di salvataggio */
		protected function generateSaveButtons() {
			if (!$this->post)
				return UIComponents::getFilledButton('Submit', 'send');
			else
				return UIComponents::getFilledButton('Save changes', 'save');
		}

		/* [Non applicabile per classe base \models\Post] */
		protected function generateActionButtons() {
			return '';
		}
	}

	class RatedPost extends Post {

		/* Genera l'indicatore del rating del post (es. voto di una recensione) */
		protected function generateRating() {
			$rating = $this->post->rating;

			return <<<EOF
			<div class="featured">
				<span class="centered rating">{$rating}</span>
			</div>
			EOF;
		}

		/* Genera il campo per inserire il rating del post (es. voto di una recensione) */
		protected function generateSpecialFields() {
			return UIComponents::getTextInput(
					'Rating',
					'rating',
					$this->post->rating ?? '',
					errors: $this->errors
			);
		}
	}

	class Comment extends RatedPost {
		protected const POST_TYPE = 'comment';

		/* Genera l'indicatore del rating del post. Override per \models\Comment. */
		protected function generateRating() {
			$icon = '';

			switch ($this->post->rating) {
				case 'ok': $icon = 'thumb_up'; break;
				case 'okma': $icon = 'thumbs_up_down'; break;
				case 'ko': $icon = 'thumb_down'; break;
			}

			$rating = UIComponents::getIcon($icon);

			return <<<EOF
			<div class="featured">
				<span class="centered rating">{$rating}</span>
			</div>
			EOF;
		}

		/* Genera un campo con la richiesta di riferimento. Override per \models\Comment. */
		protected function generateReferenceField() {
			return UIComponents::getHiddenInput('request', $this->ref);
		}

		/* Genera campi specifici dell'oggetto di riferimento. Override per \models\Comment. */
		protected function generateSpecialFields() {
			$ratings = [
				'ok' => 'ok: This content should be accepted',
				'okma' => 'okma: This content needs some work',
				'ko' => 'ko: This content should be rejected'
			];

			foreach ($ratings as $rating => $label)
				$options[] = UIComponents::getSelectOption($label, $rating);

			return UIComponents::getSelect('Rating', 'rating', $options);
		}
	}

	class Review extends RatedPost {
		protected const POST_TYPE = 'review';
	}

	class Question extends Post {
		protected const POST_TYPE = 'question';

		/* Visualizza l'oggetto di riferimento come post in rilievo, non consentendo l'inserimento
		* di nuove reazioni e mostrandone solo una selezione.
		*/
		public function displayFeatured() {
			parent::displayReference(
				active: false,
				reactions: $this->generateAnswers(featuredOnly: true)
			);
		}

		/* Visualizza l'oggetto di riferimento, inserendovi le relative risposte */
		public function display() {
			parent::displayReference(reactions: $this->generateAnswers());
		}

		/* Genera i pulsanti per elevare una domanda o inserire una risposta */
		protected function generateActionButtons() {
			$html = '';

			if ($this->session->isMod() && !$this->post->featured)
				$html .= UIComponents::getTextButton(
					'Elevate question',
					'verified',
					$this->generateURL('elevate')
				);

			if ($this->session->isAllowed())
				$html .= UIComponents::getOutlinedButton(
					'Answer',
					'comment',
					$this->generateURL('answer'),
					cls: 'colored-blue'
				);

			return $html;
		}

		/* Genera il codice per visualizzare le risposte, opzionalmente solo quelle selezionate */
		protected function generateAnswers($featuredOnly = false) {
			$html = '';

			if ($featuredOnly) {
				$answer = $this->getMapper('answers')->getFeaturedAnswer($this->post->id);

				if ($answer) {
					$view = \views\Reaction::matchModel($answer);
					$html .= $view->generateDisplay(active: false, selected: true);
				}

			} else {
				$answers = $this->post->answers;

				foreach ($answers as $answer) {
					$selected = (bool) ($answer->id == $this->post->featuredAnswer);

					$view = \views\Reaction::matchModel($answer);
					$html .= $view->generateDisplay(selected: $selected);
				}
			}

			return <<<EOF
			<div class="answers">
				{$html}
			</div>
			EOF;
		}

		/* Genera campi specifici dell'oggetto di riferimento. Override per \models\Question. */
		protected function generateSpecialFields() {
			$fields = '';

			if (!$this->post)
				return '';

			$fields .= UIComponents::getHiddenInput(
				'featured',
				$this->post->featured ? 'true' : 'false'
			);
			$fields .= UIComponents::getHiddenInput(
				'featuredAnswer',
				$this->post->featuredAnswer
			);

			return $fields;
		}
	}

	class Spoiler extends RatedPost {
		protected const POST_TYPE = 'spoiler';

		/* Visualizza l'oggetto di riferimento o un pulsante per accedervi, a seconda del
		* controller che ha inizializzato la vista.
		*/
		public function display() {
			if ($_SERVER['SCRIPT_NAME'] == '/post.php')
				parent::displayReference(content: $this->post->text);
			else
				parent::displayReference(
					active: false,
					content: UIComponents::getFilledButton(
							'Read spoiler',
							'visibility',
							$this->generateURL()
					)
				);
		}
	}

	class Extra extends Post {
		protected const POST_TYPE = 'extra';

		/* Visualizza l'oggetto di riferimento o un messaggio di errore, a seconda che l'utente
		* corrente abbia o meno la reputazione o i privilegi richiesti.
		*/
		public function display() {
			if (($this->session->getReputation() >= $this->post->reputation)
					|| $this->session->isMod())
				parent::displayReference();
			else
				parent::displayReference(
					active: false,
					content: 'You don\'t have enough reputation to read this post.'
				);
		}

		/* Genera campi specifici dell'oggetto di riferimento. Override per \models\Extra. */
		protected function generateSpecialFields() {
			return UIComponents::getTextInput(
					'Reputation',
					'reputation',
					$this->post->reputation ?? '',
					errors: $this->errors
			);
		}
	}
?>