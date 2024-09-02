<?php namespace controllers;

	class ServiceLocator {
		private static $resolvers = [];
		private static $instances = [];

		public static function register($name, $resolver) {
			static::$resolvers[$name] = $resolver;
		}

		public static function resolve($name) {
			if (isset(static::$instances[$name]))
				return static::$instances[$name];

			static::$instances[$name] = static::$resolvers[$name]();
			return static::$instances[$name];
		}
	}

?>