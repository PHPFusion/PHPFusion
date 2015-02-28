<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: SubCats.class.php
| Author: Hans Kristian Flaatten (Starefossen)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

class SubCats {
	public $catTable;
	public $catId;
	public $catName;
	public $catLeft;
	public $catRight;
	
	public $selectFields			= array("*");
	
	public $itemTable;
	public $itemCatId;
	public $itemId;
	public $itemName;
	public $itemOrder;

	// Get Single Node Data
	public function getSingleNode($nodeId) {
		return dbquery("SELECT ".$this->_getSelectFields()." FROM ".$this->catTable." WHERE ".$this->catId."='".$nodeId."' LIMIT 1");
	}

	// Get all rows in the table
	public function getFullTable() {
		return dbquery("SELECT ".$this->_getSelectFields()." FROM ".$this->catTable." ORDER BY ".$this->catId);
	}
	
	// Get full tree for a category
	public function getFullTree($id) {
		return dbquery("
			SELECT ".$this->_getSelectFields("node")." FROM ".$this->catTable." AS node,
			".$this->catTable." AS parent
			WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
			AND parent.".$this->catId."='".$id."'
			ORDER BY node.".$this->catLeft."
		");
	}
	
	// Finding all the Leaf Nodes
	public function getAllLeafNodes() {
		return dbquery("
			SELECT *
			FROM ".$this->catTable."
			WHERE ".$this->catRight."=".$this->catLeft."+1
		");
	}
	
	// Retrieving a Single Path
	public function getSinglePath($id) {
		return dbquery("
			SELECT ".$this->_getSelectFields("parent")." FROM ".$this->catTable." AS node,
			".$this->catTable." AS parent
			WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
			AND node.".$this->catId."='".$id."'
			ORDER BY parent.".$this->catLeft."
		");
	}
	
	// Finding the Depth of the Nodes
	public function getNodesDepth() {
		return dbquery("
			SELECT ".$this->_getSelectFields("node").", (COUNT(parent.".$this->catId.") - 1) AS depth
			FROM ".$this->catTable." AS node,
			".$this->catTable." AS parent
			WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
			GROUP BY node.".$this->catId."
			ORDER BY node.".$this->catLeft."
		");
	}

	// Depth of a Sub-Tree
	public function getSubTreeDepth($id) {
		return dbquery("
			SELECT ".$this->_getSelectFields("node").", (COUNT(parent.".$this->catId.") - (sub_tree.depth + 1)) AS depth
			FROM ".$this->catTable." AS node,
				".$this->catTable." AS parent,
				".$this->catTable." AS sub_parent,
				(
					SELECT node.".$this->catId.", node.".$this->catName.", (COUNT(parent.".$this->catId.") - 1) AS depth
					FROM ".$this->catTable." AS node, ".$this->catTable." AS parent
					WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
					AND node.".$this->catId."='".$id."'
					GROUP BY node.".$this->catId."
					ORDER BY node.".$this->catLeft."
				) AS sub_tree
			WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
				AND node.".$this->catLeft." BETWEEN sub_parent.".$this->catLeft." AND sub_parent.".$this->catRight."
				AND sub_parent.".$this->catId."=sub_tree.".$this->catId."
			GROUP BY node.".$this->catId."
			ORDER BY node.".$this->catLeft.";
		");
	}

	// Find the Immediate Subordinates of a Node
	public function getIntemediateSubordinates($id) {
		return dbquery("
			SELECT ".$this->_getSelectFields("node").", (COUNT(parent.".$this->catId.") - (sub_tree.depth + 1)) AS depth
			FROM ".$this->catTable." AS node,
				".$this->catTable." AS parent,
				".$this->catTable." AS sub_parent,
				(
					SELECT node.".$this->catId.", node.".$this->catName.", 
						(COUNT(parent.".$this->catId.") - 1) AS depth
					FROM ".$this->catTable." AS node, ".$this->catTable." AS parent
					WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
					AND node.".$this->catId."='".$id."'
					GROUP BY node.".$this->catId."
					ORDER BY node.".$this->catLeft."
				)AS sub_tree
			WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
				AND node.".$this->catLeft." BETWEEN sub_parent.".$this->catLeft." AND sub_parent.".$this->catRight."
				AND sub_parent.".$this->catId."=sub_tree.".$this->catId."
			GROUP BY node.".$this->catId."
			HAVING depth <= 1
			ORDER BY node.".$this->catLeft.";
		");
	}

	// Aggregate Functions in a Nested Set
	public function getCatTreeItemCount() {
		return dbquery("
			SELECT ".$this->_getSelectFields("parent").", COUNT(product.name) AS count
			FROM ".$this->catTable." AS node ,
			".$this->catTable." AS parent,
			".$this->itemTable."
			WHERE node.".$this->catLeft." BETWEEN parent.".$this->catLeft." AND parent.".$this->catRight."
			AND node.".$this->catId."=product.".$this->itemCatId."
			GROUP BY parent.".$this->catId."
			ORDER BY node.".$this->catLeft.";
		");
	}
	
	// Adding new node to the database
	public function addNode($nodeId, $nodeName, $addInside, $nodeFields, $nodeValues) {
		// Lock table
		$result = dbquery("LOCK TABLE ".$this->catTable." WRITE");

		$result = dbquery(
			"SELECT ".$this->_getSelectFields()." 
			FROM ".$this->catTable."
			WHERE ".$this->itemCatId."='".$nodeId."'
			LIMIT 1"
		);
		
		if (dbrows($result)) {
			// category_id	name	lft	rgt
			$data = dbarray($result);
			
			// Add inside
			if ($addInside == 1) {
				if ($data[$this->catLeft]+1 == $data[$this->catRight]) {
					$value = $data[$this->catLeft];
				} else {
					$value = ($data[$this->catRight]-1);
				}
			// Add bellow
			} else {
				$value = ($data[$this->catRight]);
			}

			// Update
			$this->_setNewNodeUpdate($value);
			
			// Insert new node
			$this->_setNewNodeInset($nodeName, $value, $nodeFields, $nodeValues);
		}

		// Unlock table
		$result = dbquery("UNLOCK TABLES");
	}
	
	// Update other node values
	private function _setNewNodeUpdate($value) {
		$result = dbquery(
			"UPDATE ".$this->catTable." 
			SET ".$this->catRight."=".$this->catRight."+2 
			WHERE ".$this->catRight.">".$value
		);
		$result = dbquery(
			"UPDATE ".$this->catTable." 
			SET ".$this->catLeft."=".$this->catLeft."+2 
			WHERE ".$this->catLeft.">".$value
		);
	}
	
	// Insert the new node
	private function _setNewNodeInset($nodeName, $value, $nodeFields = "", $nodeValues = "") {
		$nodeFields = ($nodeFields ? ", ".$nodeFields : "");
		$nodeValues = ($nodeValues ? ", ".$nodeValues : "");

		$result = dbquery(
			"INSERT INTO ".$this->catTable." (
				".$this->catName.", ".$this->catLeft.", ".$this->catRight.$nodeFields."
			) VALUES (
				'".$nodeName."', '".($value+1)."', '".($value+2)."'".$nodeValues."
			)"
		);	
	}
	
	// Get Select Fields
	private function _getSelectFields($prefix = "") {
		$required = array($this->catId, $this->catName, $this->catLeft, $this->catRight);
		$return = "";
		
		$prefix = ($prefix ? $prefix."." : "");
		//if ($prefix != "") { $prefix = $prefix."."}
		
		if (is_array($this->selectFields) && count($this->selectFields) > 0) {
			if (count($this->selectFields) == 1 && $this->selectFields[0] == "*") {
				$return = $prefix."*";
			} else {
				// Check the required fields
				foreach ($required as $field) {
					if (!in_array($field, $this->selectFields)) {
						$return .= ($return != "" ? ", " : "").$prefix.$field;
					}
				}
				
				// Get the other custom select fields
				foreach ($this->selectFields as $field) { 
					$return .= ($return != "" ? ", " : "").$prefix.$field;
				}	
			}
		} else {
			// If no custom field, only use required
			foreach($required as $field) {
				$return .= ($return != "" ? ", " : "").$prefix.$field;
			}
		}
		
		return $return;
	}
}
?>
