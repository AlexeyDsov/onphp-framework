<?php
	/* $Id$ */
	
	final class ObjectQueryTest extends UnitTestCase
	{
		public function test()
		{
			$oq = new ObjectQuery();
			
			$oq->
				addLogic(
					Expression::eq(DBValue::create(1), DBValue::create(1))
				)->
				addLogic(
					Expression::notEq(2, 3)
				)->
				addLogic(
					Expression::gt('id', 'bar')
				)->
				addLogic(
					Expression::gtEq('nick', 'baz')
				)->
				addLogic(
					Expression::lt('password', 'fi')
				)->
				addLogic(
					Expression::ltEq('lastLogin', 'boo')
				)->
				setLimit(28)->
				setOffset(42)->
				sort('city.id')->desc();
			
			$this->assertEqual(
				'SELECT test_user.id, test_user.nickname, test_user.password, '
				.'test_user.very_custom_field_name, test_user.registered, '
				.'test_user.strange_time, test_user.city_id, '
				.'test_user.first_optional_id, test_user.second_optional_id FROM '
				.'test_user WHERE (1 = 1) AND (2 != 3) AND (test_user.id > bar) AND '
				.'(nick >= baz) AND (test_user.password < fi) AND '
				.'(test_user.very_custom_field_name <= boo)  '
				.'ORDER BY test_user.city_id DESC '
				.'LIMIT 28 OFFSET 42',
				
				$oq->toSelectQuery(TestUser::dao())->toString()
			);
		}
	}
?>