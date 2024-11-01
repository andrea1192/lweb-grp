<?php namespace models;

	require_once('models/AbstractModel.php');
	require_once('models/Posts.php');

	abstract class AbstractMovie extends AbstractModel {
		public const ID_PREFIX = 'm';

		public $id;
		public $title = '';
		public $year = '';
		public $duration = '';
		public $summary = '';
		public $director = '';
		public $writer = '';

		public $posts;

		public function __construct($state = null) {
			if (!$state)
				return;

			$this->id = $state['id'];
			$this->title = $state['title'];
			$this->year = $state['year'];
		}
	}

	class Movie extends AbstractMovie {

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->duration = $state['duration'];
			$this->summary = $state['summary'];
			$this->director = $state['director'];
			$this->writer = $state['writer'];

			$this->posts =
					\controllers\ServiceLocator::resolve('posts')
					->getPostsByMovie($state['id']);
		}
	}

	class Request extends AbstractMovie {
		public const ID_PREFIX = 'req';
		public const REPUTATION_DELTAS = [
			'accepted' => +10
		];

		public $status = 'submitted';
		public $author;

		public function __construct($state = null) {
			if (!$state)
				return;

			parent::__construct($state);

			$this->status = $state['status'];
			$this->author = $state['author'];

			$this->duration = $state['duration'] ?? '';
			$this->summary = $state['summary'] ?? '';
			$this->director = $state['director'] ?? '';
			$this->writer = $state['writer'] ?? '';

			$this->posts =
					\controllers\ServiceLocator::resolve('comments')
					->getCommentsByRequest($state['id']);
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