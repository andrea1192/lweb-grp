<?php namespace models;

	abstract class AbstractMapper {

		public static function getDocument() {
			return \controllers\ServiceLocator::resolve(static::DOCUMENT_NAME)
					->getRepo(static::ELEMENT_NAME)
					->getDocument();
		}

		protected static function createElement($name, $source) {
			$document = static::getDocument();
			$element = $document->createElement($name);
			$element->textContent = $source[$name];
			return $element;
		}

		protected static function createAttribute($name, $source) {
			$document = static::getDocument();
			$attribute = $document->createAttribute($name);
			$attribute->value = $source[$name];
			return $attribute;
		}
	}

	abstract class AbstractMovieMapper extends AbstractMapper {

		public static function createStateFromElement($element, &$state = []) {
			$state['id'] = $element->getAttribute('id');
			$state['title'] = $element->getElementsByTagName('title')->item(0)->textContent;
			$state['year'] = $element->getElementsByTagName('year')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			if (!$element)
				$element = static::getDocument()->createElement(static::ELEMENT_NAME);

			$element->appendChild(static::createAttribute('id', $state));
			$element->appendChild(static::createElement('title', $state));
			$element->appendChild(static::createElement('year', $state));

			return $element;
		}
	}

	class MovieMapper extends AbstractMovieMapper {
		public const DOCUMENT_NAME = 'movies';
		public const ELEMENT_NAME = 'movie';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['duration'] =
					$element->getElementsByTagName('duration')->item(0)->textContent;
			$state['summary'] =
					$element->getElementsByTagName('summary')->item(0)->textContent;
			$state['director'] =
					$element->getElementsByTagName('director')->item(0)->textContent;
			$state['writer'] =
					$element->getElementsByTagName('writer')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createElement('duration', $state));
			$element->appendChild(static::createElement('summary', $state));
			$element->appendChild(static::createElement('director', $state));
			$element->appendChild(static::createElement('writer', $state));

			return $element;
		}
	}

	class RequestMapper extends AbstractMovieMapper {
		public const DOCUMENT_NAME = 'requests';
		public const ELEMENT_NAME = 'request';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['status'] = $element->getAttribute('status');
			$state['author'] = $element->getAttribute('author');

			$unavail = new class {public $textContent = '';};
			$state['duration'] =
					($element->getElementsByTagName('duration')->item(0) ?? $unavail)
					->textContent;
			$state['summary'] =
					($element->getElementsByTagName('summary')->item(0) ?? $unavail)
					->textContent;
			$state['director'] =
					($element->getElementsByTagName('director')->item(0) ?? $unavail)
					->textContent;
			$state['writer'] =
					($element->getElementsByTagName('writer')->item(0) ?? $unavail)
					->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createAttribute('status', $state));
			$element->appendChild(static::createAttribute('author', $state));

			if (!empty($state['duration']))
				$element->appendChild(static::createElement('duration', $state));
			if (!empty($state['summary']))
				$element->appendChild(static::createElement('summary', $state));
			if (!empty($state['director']))
				$element->appendChild(static::createElement('director', $state));
			if (!empty($state['writer']))
				$element->appendChild(static::createElement('writer', $state));

			return $element;
		}
	}


	class PostMapper extends AbstractMapper {
		public const DOCUMENT_NAME = 'posts';

		public static function createStateFromElement($element, &$state = []) {
			$state['id'] = $element->getAttribute('id');
			$state['status'] = $element->getAttribute('status');
			$state['movie'] = $element->getAttribute('movie');
			$state['author'] = $element->getAttribute('author');
			$state['date'] = $element->getAttribute('date');

			$state['title'] = $element->getElementsByTagName('title')->item(0)->textContent;
			$state['text'] = $element->getElementsByTagName('text')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			if (!$element)
				$element = static::getDocument()->createElement(static::ELEMENT_NAME);

			$element->appendChild(static::createAttribute('id', $state));
			$element->appendChild(static::createAttribute('status', $state));
			$element->appendChild(static::createAttribute('movie', $state));
			$element->appendChild(static::createAttribute('author', $state));
			$element->appendChild(static::createAttribute('date', $state));

			$element->appendChild(static::createElement('title', $state));
			$element->appendChild(static::createElement('text', $state));

			return $element;
		}
	}

	class CommentMapper extends PostMapper {
		public const DOCUMENT_NAME = 'comments';
		public const ELEMENT_NAME = 'comment';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['request'] = $element->getAttribute('request');
			$state['rating'] = $element->getElementsByTagName('rating')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			if (!$element)
				$element = static::getDocument()->createElement(static::ELEMENT_NAME);

			$element->appendChild(static::createAttribute('id', $state));
			$element->appendChild(static::createAttribute('status', $state));
			$element->appendChild(static::createAttribute('request', $state));
			$element->appendChild(static::createAttribute('author', $state));
			$element->appendChild(static::createAttribute('date', $state));

			$element->appendChild(static::createElement('title', $state));
			$element->appendChild(static::createElement('text', $state));

			return $element;
		}
	}

	class ReviewMapper extends PostMapper {
		public const ELEMENT_NAME = 'review';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['rating'] = $element->getElementsByTagName('rating')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$title = $element->getElementsByTagName('title')->item(0);
			$title->before(static::createElement('rating', $state));

			return $element;
		}
	}

	class QuestionMapper extends PostMapper {
		public const ELEMENT_NAME = 'question';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['featured'] = (bool) ($element->getAttribute('featured') == 'true');
			$state['featuredAnswer'] = (string) $element->getAttribute('featuredAnswer');

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$featured = static::getDocument()->createAttribute('featured');
			$featuredAnswer = static::getDocument()->createAttribute('featuredAnswer');

			$featured->value = $state['featured'] ? 'true' : 'false';
			$featuredAnswer->value = $state['featuredAnswer'];

			$element->appendChild($featured);
			$element->appendChild($featuredAnswer);

			return $element;
		}
	}

	class SpoilerMapper extends PostMapper {
		public const ELEMENT_NAME = 'spoiler';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['rating'] = $element->getElementsByTagName('rating')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$title = $element->getElementsByTagName('title')->item(0);
			$title->before(static::createElement('rating', $state));

			return $element;
		}
	}

	class ExtraMapper extends PostMapper {
		public const ELEMENT_NAME = 'extra';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['reputation'] = $element->getAttribute('reputation');

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createAttribute('reputation', $state));

			return $element;
		}
	}

	class ReactionMapper extends AbstractMapper {
		public const DOCUMENT_NAME = 'reactions';

		public static function createStateFromElement($element, &$state = []) {
			$state['post'] = $element->getAttribute('post');
			$state['author'] = $element->getAttribute('author');

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			if (!$element)
				$element = static::getDocument()->createElement(static::ELEMENT_NAME);

			$element->appendChild(static::createAttribute('post', $state));
			$element->appendChild(static::createAttribute('author', $state));

			return $element;
		}
	}

	class BinaryRatingMapper extends ReactionMapper {

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['type'] = $element->getAttribute('type');

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createAttribute('type', $state));

			return $element;
		}
	}

	class NumericRatingMapper extends ReactionMapper {

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['rating'] = $element->getAttribute('rating');

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createAttribute('rating', $state));

			return $element;
		}
	}

	class LikeMapper extends BinaryRatingMapper {
		public const ELEMENT_NAME = 'like';
	}

	class UsefulnessMapper extends NumericRatingMapper {
		public const ELEMENT_NAME = 'usefulness';
	}

	class AgreementMapper extends NumericRatingMapper {
		public const ELEMENT_NAME = 'agreement';
	}

	class SpoilageMapper extends NumericRatingMapper {
		public const ELEMENT_NAME = 'spoilage';
	}

	class AnswerMapper extends ReactionMapper {
		public const DOCUMENT_NAME = 'answers';
		public const ELEMENT_NAME = 'answer';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$state['id'] = $element->getAttribute('id');
			$state['status'] = $element->getAttribute('status');
			$state['date'] = $element->getAttribute('date');
			$state['text'] = $element->getElementsByTagName('text')->item(0)->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createAttribute('id', $state));
			$element->appendChild(static::createAttribute('date', $state));
			$element->appendChild(static::createAttribute('status', $state));

			$element->appendChild(static::createElement('text', $state));

			return $element;
		}
	}

	class ReportMapper extends ReactionMapper {
		public const DOCUMENT_NAME = 'reports';
		public const ELEMENT_NAME = 'report';

		public static function createStateFromElement($element, &$state = []) {
			parent::createStateFromElement($element, $state);

			$unavail = new class {public $textContent = '';};

			$state['date'] = $element->getAttribute('date');
			$state['status'] = $element->getAttribute('status');
			$state['message'] =
					($element->getElementsByTagName('message')->item(0) ?? $unavail)
					->textContent;
			$state['response'] =
					($element->getElementsByTagName('response')->item(0) ?? $unavail)
					->textContent;

			return $state;
		}

		public static function createElementFromState($state, &$element = null) {
			parent::createElementFromState($state, $element);

			$element->appendChild(static::createAttribute('date', $state));
			$element->appendChild(static::createAttribute('status', $state));

			$element->appendChild(static::createElement('message', $state));
			$element->appendChild(static::createElement('response', $state));

			return $element;
		}
	}

