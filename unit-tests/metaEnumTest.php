<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use StephenHarris\WordPressBehatExtension\Context\MetaData\MetaType;

class metaEnumTest extends \PHPUnit_Framework_TestCase
{

	protected function setUp()
	{
		parent::setUp();
		Monkey::setUp();
	}

	protected function tearDown()
	{
		Monkey::tearDown();
		parent::tearDown();
	}

	/**
	 * @dataProvider addMetaProvider
	 */
	public function testAddPostMeta( $meta_type, $func, $object, $key, $value, $unique ){

		Functions::expect($func)
			->with(1, $key, $value, $unique );

		$meta_type->addMeta( $object, $key, $value, $unique );
	}

	public function addMetaProvider(){

		$stubPost = (object) array( 'ID' => 1 );
		$stubUser = (object) array( 'ID' => 1 );
		$stubComment = (object) array( 'comment_ID' => 1 );
		$stubTerm = (object) array( 'term_id' => 1 );

		return array(
			'post' => array(
				new MetaType(MetaType::POST), 'add_post_meta', $stubPost, 'foo', 'bar', false
			),
			'user' => array(
				new MetaType(MetaType::USER), 'add_user_meta', $stubUser, 'favourite_song', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', false
			),
			'comment' => array(
				new MetaType(MetaType::COMMENT), 'add_comment_meta', $stubComment, 'spamicity', 55, false
			),
			'term' => array(
				new MetaType(MetaType::TERM), 'add_term_meta', $stubTerm, 'foo', 'baz', false
			)
		);
	}

	/**
	 * @dataProvider getMetaProvider
	 */
	public function testGetPostMeta( $meta_type, $func, $object, $key, $single, $return ){

		Functions::expect($func)
			->with(2, $key, $single )
			->andReturn($return);

		$actual = $meta_type->getMeta( $object, $key, $single );
		$this->assertEquals( $return, $actual );
	}

	public function getMetaProvider(){

		$stubPost = (object) array( 'ID' => 2 );
		$stubUser = (object) array( 'ID' => 2 );
		$stubComment = (object) array( 'comment_ID' => 2 );
		$stubTerm = (object) array( 'term_id' => 2 );

		return array(
			'post' => array(
				new MetaType(MetaType::POST), 'get_post_meta', $stubPost, 'foo', true, 'bar'
			),
			'user' => array(
				new MetaType(MetaType::USER), 'get_user_meta', $stubUser, 'favourite_song', true, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
			),
			'comment' => array(
				new MetaType(MetaType::COMMENT), 'get_comment_meta', $stubComment, 'spamicity', true, 55
			),
			'term' => array(
				new MetaType(MetaType::TERM), 'get_term_meta', $stubTerm, 'foo', false, array( 'baz' )
			)
		);
	}

	/**
	 * @dataProvider getAssertHasMetaKeyProvider
	 */
	public function testAssertHasMetaKey( $meta_type, $object, $func ){

		Functions::expect($func)
			->once()
			->with( 3, '', false )
			->andReturn( array( 'foo' => array( 'bar', 'baz' ), 'hello' => array( 'world', 'universe' ) ) );

		$meta_type->assertHasMetaKey($object,'foo');
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Failed asserting object has meta key "oof"
	 * @dataProvider getAssertHasMetaKeyProvider
	 */
	public function testAssertHasMetaKeyWhenItDoesNot( $meta_type, $object, $func ){

		Functions::expect($func)
			->once()
			->with( 3, '', false )
			->andReturn( array( 'foo' => array( 'bar', 'baz' ), 'hello' => array( 'world', 'universe' ) ) );

		$meta_type->assertHasMetaKey($object,'oof');
	}

	/**
	 * @dataProvider getAssertHasMetaKeyProvider
	 */
	public function testAssertHasKeyValue( $meta_type, $object, $func ){

		Functions::expect($func)
			->once()
			->with( 3, 'foo', false )
			->andReturn( array( 'bar', 'baz' ) );

		$meta_type->assertMetaKeyValue($object,'foo','bar');
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Failed asserting object has value "bar" for the meta key "foo". Found instead values: bax, baw, baz
	 * @dataProvider getAssertHasMetaKeyProvider
	 */
	public function testAssertHasKeyValueWhenItDoesNot( $meta_type, $object, $func ){

		Functions::expect($func)
			->once()
			->with( 3, 'foo', false )
			->andReturn( array( 'bax', 'baw', 'baz' ) );

		$meta_type->assertMetaKeyValue($object,'foo','bar');
	}

	public function getAssertHasMetaKeyProvider(){

		$stubPost = (object) array( 'ID' => 3 );
		$stubUser = (object) array( 'ID' => 3 );
		$stubComment = (object) array( 'comment_ID' => 3 );
		$stubTerm = (object) array( 'term_id' => 3 );

		return array(
			'post' => array(
				new MetaType(MetaType::POST), $stubPost,'get_post_meta'
			),
			'user' => array(
				new MetaType(MetaType::USER), $stubUser, 'get_user_meta'
			),
			'comment' => array(
				new MetaType(MetaType::COMMENT), $stubComment, 'get_comment_meta'
			),
			'term' => array(
				new MetaType(MetaType::TERM), $stubTerm, 'get_term_meta'
			)
		);
	}

}