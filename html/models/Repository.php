<?php namespace models;

	interface IRepository {

		public function create($type, $state);

		public function read($id);

		public function update($object);
	}