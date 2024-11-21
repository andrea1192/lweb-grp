<?php namespace models;

	require_once('connection.php');
	require_once('models/Repository.php');
	require_once('models/AbstractMapper.php');

	define('XMLNS_DEF', 'http://www.w3.org/2000/xmlns/');
	define('XSI_DEF', 'http://www.w3.org/2001/XMLSchema-instance');

	abstract class XMLDocument implements IRepository {
		protected const SCHEMAS_ROOT = DIR_SCHEMAS;
		protected const DOCUMENT_ROOT = DIR_STATIC;
		protected const DOCUMENT_NAME = '';

		protected $document;
		protected $xpath;

		private static function getSchemaPath() {
			return static::SCHEMAS_ROOT.static::DOCUMENT_NAME.'.xsd';
		}

		private static function getDocumentPath() {
			return static::DOCUMENT_ROOT.static::DOCUMENT_NAME.'.xml';
		}

		public static function getRepo($type) {
			$sl = '\\controllers\\ServiceLocator';

			switch ($type) {
				case 'movie':
					return $sl::resolve('movies');
				case 'request':
					return $sl::resolve('requests');

				case 'review':
				case 'question':
				case 'spoiler':
				case 'extra':
					return $sl::resolve('posts');
				case 'comment':
					return $sl::resolve('comments');

				case 'like':
				case 'usefulness':
				case 'agreement':
				case 'spoilage':
					return $sl::resolve('reactions');
				case 'answer':
					return $sl::resolve('answers');
				case 'report':
					return $sl::resolve('reports');
			}
		}

		public static function getMapper($type) {
			$ns = '\\models\\';

			switch ($type) {
				case 'movie':
					return $ns.'MovieMapper';
				case 'request':
					return $ns.'RequestMapper';

				case 'review':
					return $ns.'ReviewMapper';
				case 'question':
					return $ns.'QuestionMapper';
				case 'spoiler':
					return $ns.'SpoilerMapper';
				case 'extra':
					return $ns.'ExtraMapper';
				case 'comment':
					return $ns.'CommentMapper';

				case 'like':
					return $ns.'LikeMapper';
				case 'usefulness':
					return $ns.'UsefulnessMapper';
				case 'agreement':
					return $ns.'AgreementMapper';
				case 'spoilage':
					return $ns.'SpoilageMapper';
				case 'answer':
					return $ns.'AnswerMapper';
				case 'report':
					return $ns.'ReportMapper';
			}
		}

		public function __construct() {
			if (is_file(static::getDocumentPath()))
				$this->loadDocument();
		}

		public function init($source = null) {

			if (!is_dir(static::DOCUMENT_ROOT))
				mkdir(static::DOCUMENT_ROOT);

			if (!$source) {
				static::createDocument();

			} else {
				$doc = $source.static::DOCUMENT_NAME.'.xml';

				if (!is_file($doc))
					throw new \Exception("Couldn't find $doc");

				static::loadDocument($doc);
				static::saveDocument();
			}
		}

		public function restore() {
			if (is_file(static::getDocumentPath()))
				unlink(static::getDocumentPath());

			static::createDocument();
		}

		protected function createDocument() {
			$sch_path = static::getSchemaPath();

			$this->document = new \DOMDocument('1.0', 'UTF-8');

			$root = $this->document->createElement(static::DOCUMENT_NAME);
			$root->setAttributeNS(XMLNS_DEF, 'xmlns:xsi', XSI_DEF);
			$root->setAttributeNS(XSI_DEF, 'noNamespaceSchemaLocation', $sch_path);
			$this->document->appendChild($root);

			$this->saveDocument();
		}

		protected function loadDocument($doc_path = null, $sch_path = null) {
			$doc_path = $doc_path ?? static::getDocumentPath();
			$sch_path = $sch_path ?? static::getSchemaPath();

			$this->document = new \DOMDocument('1.0', 'UTF-8');

			$this->document->load($doc_path);
			$this->document->schemaValidate($sch_path);

			$this->xpath = new \DOMXPath($this->document);
		}

		protected function saveDocument($doc_path = null, $sch_path = null) {
			$doc_path = $doc_path ?? static::getDocumentPath();
			$sch_path = $sch_path ?? static::getSchemaPath();

			$this->document->schemaValidate($sch_path);
			$this->document->save($doc_path);
		}

		public function getDocument() {
			return $this->document;
		}

		public function create($type, $state) {
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);
			$session = \controllers\ServiceLocator::resolve('session');

			$state['id'] = $repo->generateID($type);
			$state['author'] = $session->getUsername();
			$state['date'] = date('c');

			$object = \models\AbstractModel::build($type, $state);
			$element = $mapper::createElementFromState($state);
			$repo->appendElement($element);

			return $object;
		}

		public function read($id) {
			$type = \models\AbstractModel::getType($id);
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);

			$element = $repo->getElement($id);

			if ($element) {
				$state = $mapper::createStateFromElement($element);
				return \models\AbstractModel::build($type, $state);
			}
		}

		public function readReaction($post, $author, $type) {
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);

			$element = $repo->getReaction($post, $author, $type);

			if ($element) {
				$state = $mapper::createStateFromElement($element);
				return \models\AbstractModel::build($type, $state);
			}
		}

		public function update($object) {
			$type = \models\AbstractModel::getType($object);
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);

			$state = $object->getState();
			$element = $mapper::createElementFromState($state);
			$repo->replaceElement($element);

			return $object;
		}

		// public function delete($object) {}

		// ========== Low-level interface ==========

		protected function getElement($id) {
			return $this->document->getElementById($id);
		}

		protected function replaceElement($element) {
			$this->document->getElementById($element->id)->replaceWith($element);
			$this->saveDocument();
		}

		protected function appendElement($node) {
			$this->document->documentElement->append($node);
			$this->saveDocument();
		}

		protected function deleteElement($id) {
			$element = $this->document->getElementById($id);
			$element->parentNode->removeChild($element);

			$this->saveDocument();
		}

		protected function generateID($type) {
			$mapper = static::getMapper($type);
			$root = $mapper::DOCUMENT_NAME;
			$elem = $mapper::ELEMENT_NAME;

			$query = "/{$root}/{$elem}/@id";
			$nodes = $this->xpath->evaluate($query);

			if (!$nodes)
				return '';

			$prefix = '';
			$largest = 0;

			foreach ($nodes as $node) {
				preg_match('/([[:alpha:]]+)([[:digit:]])/', $node->nodeValue, $matches);

				$prefix = $matches[1];
				$number = $matches[2];

				if ($number > $largest)
					$largest = $number;
			}

			return $prefix.++$largest;
		}
	}
?>