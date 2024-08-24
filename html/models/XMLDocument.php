<?php namespace models;

	abstract class XMLDocument {
		protected const SCHEMAS_ROOT = 'schemas/';
		protected const DOCUMENT_ROOT = 'static/';
		protected const DOCUMENT_NAME = '';
		protected const ROOT_ELEMENT = '';

		protected static $document;
		protected static $xpath;

		private static function getSchemaPath() {
		return static::SCHEMAS_ROOT.static::DOCUMENT_NAME.'.xsd';
		}

		private static function getDocumentPath() {
		return static::DOCUMENT_ROOT.static::DOCUMENT_NAME.'.xml';
		}

		protected static function loadDocument() {
			static::$document = new \DOMDocument('1.0', 'UTF-8');

			static::$document->load(static::getDocumentPath());
			static::$document->schemaValidate(static::getSchemaPath());
		}

		protected static function saveDocument() {
			static::$document->schemaValidate(static::getSchemaPath());
			static::$document->save(static::getDocumentPath());
		}

		protected static function queryDocument($query) {
			if (!static::$xpath) {
				static::loadDocument();
				static::$xpath = new \DOMXPath(static::$document);
			}

			return static::$xpath->query($query, static::$document);
		}

		protected static function getElementById($id) {
			if (!static::$document)
				static::loadDocument();

			return static::$document->getElementById($id);
		}

		protected static function replaceElementById($id, $node) {
			if (!static::$document)
				static::loadDocument();

			static::$document->getElementById($id)->replaceWith($node);
			static::saveDocument();
		}

		protected static function appendElement($node) {
			if (!static::$document)
				static::loadDocument();

			static::$document->documentElement->append($node);
			static::saveDocument();
		}
	}
?>