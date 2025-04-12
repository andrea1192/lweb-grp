<?php namespace models;

	abstract class AbstractMovie extends AbstractModel {
		public const ID_PREFIX = 't';

		public $id;
		public $title = '';
		public $year = '';
		public $duration = '';
		public $summary = '';
		public $director = '';
		public $writer = '';

		public $poster = '';
		public $backdrop = '';

		protected function __construct($state) {
			parent::__construct($state);

			$this->id = $this->validateString('id');
			$this->title = $this->validateString('title');
			$this->year = $this->validateNumeric('year');

			$this->poster = $state['poster'] ?? '';
			$this->backdrop = $state['backdrop'] ?? '';

			$this->checkValidation();
		}
	}

	class Movie extends AbstractMovie {

		protected function __construct($state) {
			parent::__construct($state);

			$this->duration = $this->validateNumeric('duration');
			$this->summary = $this->validateString('summary');
			$this->director = $this->validateString('director');
			$this->writer = $this->validateString('writer');

			$this->checkValidation();
		}
	}

	class Request extends AbstractMovie {
		public const REPUTATION_DELTAS = [
			'accepted' => +10
		];

		public $status = 'submitted';
		public $author;

		protected function __construct($state) {
			parent::__construct($state);

			$this->status = $this->validateString('status');
			$this->author = $this->validateString('author');

			$this->duration = $this->validateNumeric('duration', required: false);
			$this->summary = $this->validateString('summary', required: false);
			$this->director = $this->validateString('director', required: false);
			$this->writer = $this->validateString('writer', required: false);

			$this->checkValidation();
		}

		public function setStatus($status) {
			switch ($status) {
				case 'submitted':
				case 'accepted':
				case 'rejected':
				case 'deleted':
					$this->status = $status;
					break;
			}
		}
	}
?>