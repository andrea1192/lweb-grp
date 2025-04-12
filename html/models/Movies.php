<?php namespace models;

	class Threads extends Table {
		public const MEDIA_TYPES = MEDIA_TYPES;
	}

	class Movies extends Threads {
		protected const DB_VIEW = 'VThreadsAccepted';
		protected const DB_TABLE = '';
		protected const DB_ATTRIBS = [];
		protected const DB_SCHEMA = <<<EOF
		CREATE VIEW IF NOT EXISTS VThreadsAccepted AS
			SELECT
				id,
				title,
				year,
				author,
				date,
				duration,
				summary,
				director,
				writer,
				poster,
				backdrop
			FROM Threads
			WHERE status = 'accepted'
		EOF;
		protected const OB_TYPE = 'movie';
		protected const OB_PRI_KEY = 'id';

		public function getMovies() {
			return $this->readAll();
		}

		public function getMovieById($id) {
			return $this->read($id);
		}
	}

	class Requests extends Threads {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Threads';
		protected const DB_ATTRIBS = [
			'id',
			'status',
			'title',
			'year',
			'author',
			'date',
			'duration',
			'summary',
			'director',
			'writer',
			'poster',
			'backdrop'
		];
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Threads (
			id 			VARCHAR(80)		PRIMARY KEY,
			status 		SET(
				'submitted',
				'accepted',
				'rejected',
				'deleted'
			) DEFAULT 'submitted',
			title		VARCHAR(160)	NOT NULL,
			year		YEAR			NOT NULL,
			author		VARCHAR(160)	NOT NULL,
			date		TIMESTAMP		DEFAULT CURRENT_TIMESTAMP,
			duration	SMALLINT,
			summary		TEXT,
			director	VARCHAR(160),
			writer		VARCHAR(160),
			poster 		MEDIUMBLOB,
			backdrop 	MEDIUMBLOB
		)
		EOF;
		protected const OB_TYPE = 'request';
		protected const OB_PRI_KEY = 'id';

		public function getRequests() {
			return $this->readAll();
		}

		private function getRequestsByStatus($status) {
			$matches = $this->sql_select(DB_TABLE, ['status' => $status]);

			foreach ($matches as $match)
				$requests[] = new \models\Request($match);

			return $requests;
		}

		public function getSubmittedRequests() {
			return $this->getRequestsByStatus('submitted');
		}

		public function getAcceptedRequests() {
			return $this->getRequestsByStatus('accepted');
		}

		public function getRejectedRequests() {
			return $this->getRequestsByStatus('rejected');
		}

		public function getRequestById($id) {
			return $this->read($id);
		}
	}
?>