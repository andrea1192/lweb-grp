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
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Posts (
			id 			VARCHAR(80)		PRIMARY KEY,
			status 		ENUM(
				'active',
				'deleted'
			) DEFAULT 'active',
			movie		VARCHAR(80)		NOT NULL REFERENCES Threads(id),
			author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
			date		TIMESTAMP		DEFAULT CURRENT_TIMESTAMP,
			title		VARCHAR(160),
			text		TEXT
			)
		EOF;
		protected const OB_TYPE = '';
		protected const OB_PRI_KEY = 'id';

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
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Reviews (
			id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT,
			rating		INT 			NOT NULL,
			CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 10)
		);
		CREATE VIEW IF NOT EXISTS VReviews AS
			SELECT *
			FROM Posts NATURAL JOIN Reviews
		;
		EOF;
		protected const OB_TYPE = 'review';
		protected const OB_PRI_KEY = 'id';
	}

	class QA extends Posts {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'QA';
		protected const DB_ATTRIBS = [
				'id'
		];
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS QA (
			id 				VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT
		);
		EOF;
	}

	class Questions extends QA {
		protected const DB_VIEW = 'VQuestions';
		protected const DB_TABLE = 'Questions';
		protected const DB_ATTRIBS = [
				'id',
				'featured',
				'featuredAnswer'
		];
		// (*): Il vincolo di integrità su featuredAnswer (chiave esterna riferita ad Answers(id))
		// non può essere aggiunto subito perchè Answers, contenendo a sua volta un riferimento a
		// questa tabella, deve necessariamente essere creata in seguito
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Questions (
			id 				VARCHAR(80)		PRIMARY KEY REFERENCES QA(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT,
			featured 		BOOLEAN			DEFAULT FALSE,
			featuredAnswer	VARCHAR(80)		-- REFERENCES Answers(id) da aggiungere dopo (*)
		);
		CREATE VIEW IF NOT EXISTS VQuestions AS
			SELECT *
			FROM Posts NATURAL JOIN Questions
		;
		EOF;
		protected const OB_TYPE = 'question';
		protected const OB_PRI_KEY = 'id';

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

	class Answers extends QA {
		protected const DB_VIEW = 'VAnswers';
		protected const DB_TABLE = 'Answers';
		protected const DB_ATTRIBS = [
				'id',
				'post'
		];
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Answers (
			id 			VARCHAR(80)		PRIMARY KEY REFERENCES QA(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT,
			post 		VARCHAR(80)		NOT NULL REFERENCES Questions(id)
		);
		CREATE VIEW IF NOT EXISTS VAnswers AS
			SELECT *
			FROM Posts NATURAL JOIN Answers
		;
		ALTER TABLE Questions
			ADD CONSTRAINT featuredAnswer_fk
				FOREIGN KEY IF NOT EXISTS (featuredAnswer) REFERENCES Answers(id)
					ON DELETE SET NULL
					ON UPDATE RESTRICT
		;
		EOF;
		protected const OB_TYPE = 'answer';
		protected const OB_PRI_KEY = 'id';

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
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Spoilers (
			id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT,
			rating		INT 			NOT NULL,
			CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 10)
		);
		CREATE VIEW IF NOT EXISTS VSpoilers AS
			SELECT *
			FROM Posts NATURAL JOIN Spoilers
		;
		EOF;
		protected const OB_TYPE = 'spoiler';
		protected const OB_PRI_KEY = 'id';
	}

	class Extras extends Posts {
		protected const DB_VIEW = 'VExtras';
		protected const DB_TABLE = 'Extras';
		protected const DB_ATTRIBS = [
				'id',
				'reputation'
		];
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Extras (
			id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT,
			reputation	INT 			NOT NULL
		);
		CREATE VIEW IF NOT EXISTS VExtras AS
			SELECT *
			FROM Posts NATURAL JOIN Extras
		;
		EOF;
		protected const OB_TYPE = 'extra';
		protected const OB_PRI_KEY = 'id';
	}

	class Comments extends Posts {
		protected const DB_VIEW = 'VComments';
		protected const DB_TABLE = 'Comments';
		protected const DB_ATTRIBS = [
				'id',
				'rating'
		];
		protected const DB_SCHEMA = <<<EOF
		CREATE TABLE IF NOT EXISTS Comments (
			id 			VARCHAR(80)		PRIMARY KEY REFERENCES Posts(id)
												ON DELETE CASCADE
												ON UPDATE RESTRICT,
			rating		ENUM(
					'ok',
					'okma',
					'ko'
			) NOT NULL
		);
		CREATE VIEW IF NOT EXISTS VComments AS
			SELECT *
			FROM Posts NATURAL JOIN Comments
		;
		EOF;
		protected const OB_TYPE = 'comment';
		protected const OB_PRI_KEY = 'id';

		public function getCommentsByRequest($movie_id) {
			return $this->getPostsByMovie($movie_id);
		}

		public function getCommentById($id) {
			return $this->read($id);
		}
	}
?>