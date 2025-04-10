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
					movie		VARCHAR(80)		NOT NULL REFERENCES Threads(id),
					author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
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
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
														ON DELETE CASCADE
														ON UPDATE RESTRICT,
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

			// NOTA: Il vincolo di integrità su featuredAnswer (chiave esterna riferita ad Answers)
			// non può essere aggiunto subito perchè Answers, contenendo un riferimento NOT NULL a
			// questa tabella, deve necessariamente essere creata in seguito
			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Questions (
					id 				VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
														ON DELETE CASCADE
														ON UPDATE RESTRICT,
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

	class Answers extends Posts {
		protected const DB_VIEW = 'VAnswers';
		protected const DB_TABLE = 'Answers';
		protected const DB_ATTRIBS = [
				'id',
				'post'
		];
		protected const OB_TYPE = 'answer';
		protected const OB_PRI_KEY = 'id';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Answers (
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
														ON DELETE CASCADE
														ON UPDATE RESTRICT,
					post 		VARCHAR(80)		NOT NULL REFERENCES Questions(id)
					)
					EOF
			);
			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VAnswers AS
							SELECT *
							FROM Posts NATURAL JOIN Answers
					EOF
			);
			$this->query(<<<EOF
					ALTER TABLE Questions
					ADD CONSTRAINT featuredAnswer_fk
							FOREIGN KEY IF NOT EXISTS (featuredAnswer) REFERENCES Answers(id)
									ON DELETE SET NULL
									ON UPDATE RESTRICT
					EOF
			);
		}

		public function getFeaturedAnswer($post_id) {
			$answer_id =
					\controllers\ServiceLocator::resolve('questions')
					->getPostById($post_id)->featuredAnswer;

			if ($answer_id)
				return $this->getAnswerById($answer_id);
		}

		public function getAnswersByPost($post_id) {
			$criteria = ['post' => $post_id];
			$matches = $this->sql_select(static::DB_VIEW, $criteria);
			$objects = [];

			foreach ($matches as $match)
				$objects[] = \models\AbstractModel::build(static::OB_TYPE, $match);

			return $objects;
		}

		public function getAnswerById($id) {
			return $this->read($id);
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
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
														ON DELETE CASCADE
														ON UPDATE RESTRICT,
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
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
														ON DELETE CASCADE
														ON UPDATE RESTRICT,
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
					id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
														ON DELETE CASCADE
														ON UPDATE RESTRICT,
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