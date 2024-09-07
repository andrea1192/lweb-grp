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

		public function save($object) {
			$mapper = static::getMapperForItem($object);

			if (empty($object->id)) {
				$root = $mapper::DOCUMENT_NAME;
				$elem = $mapper::ELEMENT_NAME;
				$prefix = $mapper::ID_PREFIX;

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