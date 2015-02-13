<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */

namespace core\Db;

use core\Utils\Arrays as Arrays;

class Mongo {
	/**
	 * @var array Mongo connection data
	 */
	private $data = array();

	/**
	 * @var Object connection
	 */
	private $connection = null;

	/**
	 * @var Object mongo db (with the connection)
	 */
	private $db = null;

	public function __construct(array $connectionData) {
		
		// Check if all the needed data is in the array
		if(!Arrays::keysExists(array("db", "host"), $connectionData)) {
			throw new MongoException("Missing connection data");
		}

		// Establish MongoDB connection
		$this->connection = new \MongoClient($connectionData["host"]);

		// Selecting the database
		$this->db = $this->connection->selectDB($connectionData["db"]);
	}

	/**
	 * Select data from the database
	 *
	 * @param string $collection The collection name
	 * @param array $find Where conditions
	 * @return Object Returns MongoDB cursor.
	 */
	public function find($collection, $find = array(), array $showFields = array()) {
		$fields = array();

		if(count($showFields) > 0) {
			foreach ($showFields as $fieldName) {
			 	$fields[$fieldName] = 1;
			 } 
		}

		if(!array_key_exists("_id", $fields)) {
			$fields["_id"] = 0;
		}

		return $this->db->$collection->find($find, $fields);
	}

	/**
	 * Select data from the database
	 *
	 * @param string $collection The collection name
	 * @param array $find Where conditions
	 * @return Object Returns MongoDB cursor.
	 */
	public function findOne($collection, $find = array(), array $showFields = array()) {
		$fields = array();

		if(count($showFields) > 0) {
			foreach ($showFields as $fieldName) {
			 	$fields[$fieldName] = 1;
			 } 
		}

		if(!array_key_exists("_id", $fields)) {
			$fields["_id"] = 0;
		}

		return $this->db->$collection->findOne($find, $fields);
	}

	/**
	 * Converts a MongoDB cursor into array.
	 *
	 * @param \MongoCursor The mongo cursor
	 * @return array 
	 */
	public function cursorAsArray(\MongoCursor $cursor) {
		$result = array();

		foreach ($cursor as $document) {
			array_push($result, $document);
		}

		return $result;
	}

	/**
	 * Converts a MongoDB cursor into json string.
	 *
	 * @param \MongoCursor The mongo cursor
	 * @return string 
	 */
	public function cursorAsJSON(\MongoCursor $cursor) {
		$json = json_encode($this->cursorAsArray($cursor));

		return $json;
	}

	/**
	 * Inserts data into the given collection
	 *
	 * @param string $collection The name of the collection
	 * @param array $data the data that should be inserted
	 */
	public function insert($collection, array $data) {
		$this->db->$collection->insert($data);
		$status = $this->getLastError();

		return ($status["ok"] == 1) ? true : false;
	}

	/**
	 * Updates all matching documents
	 *
	 * @param string $collection The name of the collection
	 * @param array $where Where conditions
	 * @param array $data Fields that should be updated and the value
	 * @return int The number of affected documents
	 */
	public function update($collection, array $where, array $data) {
		return $this->_update($collection, $where, $data, true);
	}

	/**
	 * Update one matching document. (The first document found)
	 *
	 * @param string $collection The name of the collection
	 * @param array $where Where conditions
	 * @param array $data Fields that should be updated and the value
	 * @return int The number of affected documents
	 */
	public function updateOne($collection, array $where, array $data) {
		return $this->_update($collection, $where, $data, false);
	}

	/**
	 * Updates matching documents
	 *
	 * @param string $collection The name of the collection
	 * @param array $where Where conditions
	 * @param array $data Fields that should be updated and the value
	 * @param boolean $multi TRUE for multiple documents update and FALSE for single document update
	 * @return int The number of affected documents
	 */
	private function _update($collection, array $where, array $data, $multi = true) {
		$this->db->$collection->update($where, array('$set' => $data), array("multiple" => $multi));
		$status = $this->getLastError();

		return $status["n"];
	}

	/**
	 * Returns the last error on the most recent db operation performed
	 *
	 * @return array The last error.
	 */
	private function getLastError() {
		return $this->db->lastError();
	}
}
?>