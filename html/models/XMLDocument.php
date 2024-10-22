<?php namespace models;

	abstract class XMLDocument {
		protected const SCHEMAS_ROOT = 'schemas/';
		protected const DOCUMENT_ROOT = 'static/';
		protected const DOCUMENT_NAME = '';

		protected $document;
		protected $xpath;

		private static function getSchemaPath() {
			return static::SCHEMAS_ROOT.static::DOCUMENT_NAME.'.xsd';
		}

		private static function getDocumentPath() {
			return static::DOCUMENT_ROOT.static::DOCUMENT_NAME.'.xml';
		}

		protected static function getMapper($mapper) {
			return \controllers\ServiceLocator::resolve($mapper);
		}

		public static function getMapperForType($type) {

			switch ($type) {
				case 'movie':
					$mapper = 'movies';
					break;
				case 'request':
					$mapper = 'requests';
					break;

				case 'review':
				case 'question':
				case 'spoiler':
				case 'extra':
					$mapper = 'posts';
					break;
				case 'comment':
					$mapper = 'comments';
					break;

				case 'like':
				case 'usefulness':
				case 'agreement':
				case 'spoilage':
					$mapper = 'reactions';
					break;
				case 'answer':
					$mapper = 'answers';
					break;
				case 'report':
					$mapper = 'reports';
					break;
			}

			return \controllers\ServiceLocator::resolve($mapper);
		}

		public function __construct() {
			$this->loadDocument();
		}

		protected function loadDocument() {
			$this->document = new \DOMDocument('1.0', 'UTF-8');

			$this->document->load(static::getDocumentPath());
			$this->document->schemaValidate(static::getSchemaPath());

			$this->xpath = new \DOMXPath($this->document);
		}

		protected function saveDocument() {
			$this->document->schemaValidate(static::getSchemaPath());
			$this->document->save(static::getDocumentPath());
		}

		public function select($id) {
			$type = Movie::getType($id)
					?? Post::getType($id)
					?? Reaction::getType($id);

			$mapper = static::getMapperForType($type);
			$element = $mapper->document->getElementById($id);

			$mapper = $mapper::getMapperForItem($element);
			return $mapper::createObjectFromElement($element);
		}

		public function save($object) {
			$mapper = static::getMapperForItem($object);

			if (empty($object->id)) {
				$root = $mapper::DOCUMENT_NAME;
				$elem = $mapper::ELEMENT_NAME;
				$prefix = $object::ID_PREFIX;

				$object->id = $this->generateID($root, $elem, $prefix);
			}

			$element = $mapper::createElementFromObject($object, $this->document);

			if ($this->document->getElementById($object->id))
				$this->replaceElement($object->id, $element);
			else
				$this->appendElement($element);

			return $object;
		}

		protected function replaceElement($id, $node) {
			$this->document->getElementById($id)->replaceWith($node);
			$this->saveDocument();
		}

		protected function appendElement($node) {
			$this->document->documentElement->append($node);
			$this->saveDocument();
		}

		public function delete($id) {
			$element = $this->document->getElementById($id);
			$element->parentNode->removeChild($element);

			$this->saveDocument();
		}

		protected function generateID($root, $element, $prefix) {
			$query = "/{$root}/{$element}/@id";
			$nodes = $this->xpath->evaluate($query);

			$largest = 0;

			foreach ($nodes as $node) {
				$id = $node->nodeValue;
				$id = preg_replace("/{$prefix}/", '', $id);

				if ($id > $largest)
					$largest = $id;
			}

			return $prefix.++$largest;
		}
	}
?>