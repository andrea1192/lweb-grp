<?php namespace models;

	class Posts extends Table {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Posts';
		protected const DB_ATTRIBS = [
				'id',
				'status',
				'movie',
				'author',
				'date',
				'title',
				'text'
		];
		protected const OB_TYPE = '';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Posts (
					id 			VARCHAR(80)		PRIMARY KEY,
					status 		SET(
							'active',
							'deleted'
					) DEFAULT 'active',
					movie		VARCHAR(80)		NOT NULL,
					author		VARCHAR(160)	NOT NULL,
					date		TIMESTAMP		DEFAULT CURRENT_TIMESTAMP,
					title		VARCHAR(160),
					text		TEXT
					)
					EOF
			);
		}

		public function getPostsByMovie($movie_id) {
			$criteria = [
					'movie' => $movie_id,
					'status' => 'active'
			];
			$matches = $this->sql_select(static::DB_VIEW, $criteria);
			$objects = [];

			foreach ($matches as $match)
				$objects[] = \models\AbstractModel::build(static::OB_TYPE, $match);

			return $objects;
		}

		public function getPostById($id) {
			return $this->read($id);
		}
	}

	class Reviews extends Posts {
		protected const DB_VIEW = 'VReviews';
		protected const DB_TABLE = 'Reviews';
		protected const DB_ATTRIBS = [
				'id',
				'rating'
		];
		protected const OB_TYPE = 'review';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Reviews (
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id),
					rating		INT 			NOT NULL,
					CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 10)
					)
					EOF
			);
			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VReviews AS
							SELECT *
							FROM Posts NATURAL JOIN Reviews
					EOF
			);
		}
	}

	class Questions extends Posts {
		protected const DB_VIEW = 'VQuestions';
		protected const DB_TABLE = 'Questions';
		protected const DB_ATTRIBS = [
				'id',
				'featured',
				'featuredAnswer'
		];
		protected const OB_TYPE = 'question';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Questions (
					id 				VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id),
					featured 		BOOLEAN			DEFAULT FALSE,
					featuredAnswer	VARCHAR(80)
					)
					EOF
			);
			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VQuestions AS
							SELECT *
							FROM Posts NATURAL JOIN Questions
					EOF
			);
		}

		public function getFeaturedQuestions($movie_id) {
			$criteria = [
					'movie' => $movie_id,
					'featured' => '1'
			];
			$matches = $this->sql_select(static::DB_VIEW, $criteria);
			$objects = [];

			foreach ($matches as $match)
				$objects[] = \models\AbstractModel::build(static::OB_TYPE, $match);

			return $objects;
		}
	}

	class Spoilers extends Posts {
		protected const DB_VIEW = 'VSpoilers';
		protected const DB_TABLE = 'Spoilers';
		protected const DB_ATTRIBS = [
				'id',
				'rating'
		];
		protected const OB_TYPE = 'spoiler';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Spoilers (
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id),
					rating		INT 			NOT NULL,
					CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 10)
					)
					EOF
			);
			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VSpoilers AS
							SELECT *
							FROM Posts NATURAL JOIN Spoilers
					EOF
			);
		}
	}

	class Extras extends Posts {
		protected const DB_VIEW = 'VExtras';
		protected const DB_TABLE = 'Extras';
		protected const DB_ATTRIBS = [
				'id',
				'reputation'
		];
		protected const OB_TYPE = 'extra';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Extras (
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id),
					reputation	INT 			NOT NULL
					)
					EOF
			);
			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VExtras AS
							SELECT *
							FROM Posts NATURAL JOIN Extras
					EOF
			);
		}
	}

	class Comments extends Posts {
		protected const DB_VIEW = 'VComments';
		protected const DB_TABLE = 'Comments';
		protected const DB_ATTRIBS = [
				'id',
				'rating'
		];
		protected const OB_TYPE = 'comment';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Comments (
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id),
					rating		SET(
							'ok',
							'okma',
							'ko'
					) NOT NULL
					)
					EOF
			);
			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VComments AS
							SELECT *
							FROM Posts NATURAL JOIN Comments
					EOF
			);
		}

		public function getCommentsByRequest($movie_id) {
			return $this->getPostsByMovie($movie_id);
		}

		public function getCommentById($id) {
			return $this->read($id);
		}
	}
?>