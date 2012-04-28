<?php
	/* $Id$ */
	
	class TransactionTest extends TestTables
	{	
		public function create()
		{
			/**
			 * @see testRecursionObjects() and meta
			 * for TestParentObject and TestChildObject
			**/
			$this->schema->
				getTableByName('test_parent_object')->
				getColumnByName('root_id')->
				dropReference();
			
			return parent::create();
		}
		
		public function testData()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);
				$this->simpleTransaction();
				
				$this->innerTransactions();
			}
			
			$this->drop();
		}
		
		private function simpleTransaction()
		{
			$idList = $this->insertAndCommit();
			$idList += $this->insertAndRollback();
		}
		
		private function innerTransactions()
		{
			$dao = TestItem::dao();
			
			$idList = $this->externalCommit();
			$idList = $this->externalRollback();
			
			$itemList = $dao->getListByIds($idList);
			$this->assertEquals(count($idList), count($itemList));
		}
		
		/**
		 * @return array
		**/
		private function externalCommit()
		{
			$dao = TestItem::dao();
			$db = DBPool::getByDao($dao);
			$db->begin();
			
			$idList = $this->insertAndCommit();
			$idList += $this->insertAndRollback();
			
			$db->commit();
			
			$itemList = $dao->getListByIds($idList);
			$this->assertEquals(count($idList), count($itemList));
			
			return $idList;
		}
		
		/**
		 * @return array
		**/
		private function externalRollback()
		{
			$dao = TestItem::dao();
			$db = DBPool::getByDao($dao);
			$db->begin();
			
			$idList = $this->insertAndCommit();
			$idList += $this->insertAndRollback();
			
			$db->rollback();
			$dao->uncacheByIds($idList);
			
			$itemList = $dao->getListByIds($idList);
			$this->assertTrue(empty($itemList));
			
			return array();
		}
		
		/**
		 * @return array
		**/
		private function insertAndCommit()
		{
			$dao = TestItem::dao();
			$db = DBPool::getByDao($dao);
			$db->begin();
			
			$item = $dao->add(TestItem::create()->setName('someItem'));
			$itemId = $item->getId();
			
			$db->commit();
			
			try {
				$dao->getById($itemId);
			} catch (ObjectNotFoundException $e) {
				$this->fail('Object must be saved');
			}
			
			return array($itemId);
		}
		
		/**
		 * @return array
		**/
		private function insertAndRollback()
		{
			$dao = TestItem::dao();
			$db = DBPool::getByDao($dao);
			$db->begin();
			
			$item = $dao->add(TestItem::create()->setName('someItem'));
			$itemId = $item->getId();
			
			$db->rollback();
			$dao->uncacheById($itemId);
			
			try {
				$dao->getById($itemId);
				$this->fail('Object must not be saved');
			} catch (ObjectNotFoundException $e) {
				/* all ok */
			}
			
			return array();
		}
	}
?>