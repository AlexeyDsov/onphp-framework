<?php
	final class PrimitiveStringTest extends TestCase
	{
		public function testImport()
		{
			$primitive = Primitive::string('stringPrimitive');
			
			$this->assertNull($primitive->importValue(null));
			$this->assertNull($primitive->importValue(''));
			
			$this->assertTrue($primitive->importValue('some string'));
			$this->assertEquals('some string', $primitive->getValue());
			
			try {
				$primitive->importValue(new stdClass());
			} catch (BaseException $e) {
				var_dump(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
				exit;
				/* all ok */
			}
		}
	}
?>