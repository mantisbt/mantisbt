<?php declare(strict_types=1);
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You must have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test cases for Tokens API within mantis
 *
 * Cache usage is checked by counting the number of database queries using
 * the db_count_queries() function.
 *
 * @package    Tests
 * @subpackage TokensAPI
 * @copyright  Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for Tokens API
 */
final class TokensApiTest extends MantisCoreBase {

	protected const UNKNOWN = -1; # a guaranteed unknown value for an owner, type, or id.
	protected const KNOWN_OWNER = 1234;
	
	protected const ROW_ID = 0;
	protected const ROW_OWNER = 1;
	protected const ROW_TYPE = 2;
	protected const ROW_VALUE = 3;
	protected const ROW_TIMESTAMP = 4;
	protected const ROW_EXPIRY = 5;

	/**
	 * Test tokens inserted in the database
	 */
	protected array $tokens;

	/**
	 * Prepare tokens for tests
	 * @return void
	 */
	protected function setUp(): void {
		global $g_cache_token, $g_tokens_purged;
		$g_cache_token = []; # clear cache
		$g_tokens_purged = true; # disable auto-purging

		# The order of the array keys must match the order of the rows in the database schema
		$t_now = db_now();
		$this->tokens = [
			[ self::KNOWN_OWNER, TOKEN_USER, 'normal_user',  $t_now, $t_now + TOKEN_EXPIRY ],
			[ self::KNOWN_OWNER, TOKEN_FILTER, 'expired_filter', $t_now, $t_now - 1 ],
			[ self::KNOWN_OWNER, TOKEN_UNKNOWN, 'expired_unknown', $t_now, $t_now - 1 ],
		];

		# Insert test tokens
		foreach( $this->tokens as & $t_token ) {
			db_param_push();
			db_query( 'INSERT INTO {tokens} ( owner, type, value, timestamp, expiry ) VALUES ( '
				. db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )'
				, $t_token );
			$t_token = array_merge( [ db_insert_id( db_get_table( 'tokens' ) ) ], $t_token );
		}
		unset( $t_token );
	}

	/**
	 * Cleanup test tokens
	 * @return void
	 */
	protected function tearDown(): void {
		global $g_cache_token, $g_tokens_purged;
		$g_cache_token = []; # clear cache
		$g_tokens_purged = false; # reset auto-purging

		foreach( $this->tokens as $t_token ) {
			db_param_push();
			db_query( 'DELETE FROM {tokens} WHERE id = '. db_param(), [ $t_token[self::ROW_ID] ] );
		}
	}

	/**
	 * Tests token_exists()
	 * @return void
	 */
	public function testTokenExists(): void {
		# positive result
		$this->assertTrue( token_exists( $this->tokens[0][self::ROW_ID] ) );

		# negative result
		$this->assertFalse( token_exists( self::UNKNOWN ) );
	}

	/**
	 * Tests token_ensure_exists()
	 * @return void
	 */
	public function testTokenEnsureExists(): void {
		# positive result
		token_ensure_exists( $this->tokens[0][self::ROW_ID] );

		# negative result
		$this->expectException( \Mantis\Exceptions\ClientException::class );
		$this->expectExceptionCode( ERROR_TOKEN_NOT_FOUND );
		token_ensure_exists( self::UNKNOWN );
	}

	/**
	 * Tests token_get()
	 * @return void
	 */
	public function testTokenGet(): void {
		# Database positive result
		$t_db_count = db_count_queries();
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The token must exist.' );
		$this->assertEquals( array_values( $this->tokens[0] ), array_values( $t_token ), 'The token must exist.' );
		$this->assertTrue( $t_db_count < db_count_queries(), 'Existing token must be verified in the database.' );

		# Cache positive result
		$t_db_count = db_count_queries();
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The token must exist.' );;
		$this->assertEquals( array_values( $this->tokens[0] ), array_values( $t_token ), 'The token must exist.' );
		$this->assertTrue( $t_db_count == db_count_queries(), 'Existing token must be verified in the cache.' );

		# Database negative result
		$t_db_count = db_count_queries();
		$t_token = token_get( TOKEN_USER, self::UNKNOWN );
		$this->assertNull( $t_token, 'The token must not exist.' );
		$this->assertTrue( $t_db_count < db_count_queries(), 'A non-existent token must be verified in the database.' );

		# Cache negative result
		$t_db_count = db_count_queries();
		$t_token = token_get( TOKEN_USER, self::UNKNOWN );
		$this->assertNull( $t_token, 'The token must not exist.' );
		$this->assertTrue( $t_db_count == db_count_queries(), 'A non-existent token must be verified in the cache.' );
	}

	/**
	 * Tests token_get_value()
	 * @return void
	 */
	public function testTokenGetValue(): void {
		# Database positive result
		$t_db_count = db_count_queries();
		$t_value = token_get_value( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertEquals( $this->tokens[0][self::ROW_VALUE], $t_value, 'The token must exist.' );
		$this->assertTrue( $t_db_count < db_count_queries(), 'Existing token must be verified in the database.' );

		# Cache positive result
		$t_db_count = db_count_queries();
		$t_value = token_get_value( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertEquals( $this->tokens[0][self::ROW_VALUE], $t_value, 'The token must exist.' );
		$this->assertTrue( $t_db_count == db_count_queries(), 'Existing token must be verified in the cache.' );

		# Database negative result
		$t_db_count = db_count_queries();
		$t_value = token_get_value( TOKEN_USER, self::UNKNOWN );
		$this->assertNull( $t_value , 'The token must not exist.' );
		$this->assertTrue( $t_db_count < db_count_queries(), 'A non-existent token must be verified in the database.' );

		# Cache negative result
		$t_db_count = db_count_queries();
		$t_value = token_get_value( TOKEN_USER, self::UNKNOWN );
		$this->assertNull( $t_value , 'The token must not exist.' );
		$this->assertTrue( $t_db_count == db_count_queries(), 'A non-existent token must be verified in the cache.' );
	}

	/**
	 * Tests token_get_by_type()
	 * Caching is not supported
	 * @return void
	 */
	public function testTokenGetByType(): void {
		# Database positive result
		$t_db_count = db_count_queries();
		$t_tokens = token_get_by_type( $this->tokens[0][self::ROW_TYPE] );
		$this->assertArrayHasKey( 0, $t_tokens, 'The token must exist.' );
		$this->assertEquals( array_values( $this->tokens[0] ), array_values( $t_tokens[0] ), 'The token must exist.' );
		$this->assertTrue( $t_db_count < db_count_queries(), 'Existing token must be verified in the database.' );

		# Database negative result
		$t_db_count = db_count_queries();
		$t_tokens = token_get_by_type( self::UNKNOWN );
		$this->assertTrue( empty( $t_tokens ), 'The token must not exist.' );
		$this->assertTrue( $t_db_count < db_count_queries(), 'A non-existent token must be verified in the database.' );
	}

	/**
	 * Tests token_purge_expired() for type
	 * @return void
	 */
	public function testTokenPurgeExpiredByType(): void {
		# load cache
		$t_token1 = token_get( $this->tokens[1][self::ROW_TYPE], $this->tokens[1][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token1, 'Expired token #1 must exist.' );
		$this->assertEquals( $t_token1['id'], $this->tokens[1][self::ROW_ID], 'Expired token #1 must exist.' );
		$t_token2 = token_get( $this->tokens[2][self::ROW_TYPE], $this->tokens[2][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token2, 'Expired token #2 must exist.' );
		$this->assertEquals( $t_token2['id'], $this->tokens[2][self::ROW_ID], 'Expired token #2 must exist.' );

		# Purge expired by type
		token_purge_expired( $this->tokens[1][self::ROW_TYPE] );
		$t_db_count = db_count_queries();
		$this->assertFalse( token_exists( $this->tokens[1][self::ROW_ID] ), ( $t_db_count == db_count_queries() )
			? 'Expired token #1 must be purged from cache.'
			: 'Expired token #1 must be purged from database.' );
		$this->assertTrue( token_exists( $this->tokens[2][self::ROW_ID] ), 'Expired token #2 must be purged by correct type only.' );
	}

	/**
	 * Tests token_purge_expired() for all
	 * @return void
	 */
	public function testTokenPurgeExpiredAll(): void {
		# load cache
		$t_token1 = token_get( $this->tokens[1][self::ROW_TYPE], $this->tokens[1][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token1, 'Expired token #1 must exist.' );
		$t_token2 = token_get( $this->tokens[2][self::ROW_TYPE], $this->tokens[2][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token2, 'Expired token #2 must exist.' );

		# Purge all expired
		token_purge_expired();
		$t_db_count = db_count_queries();
		$this->assertFalse( token_exists( $this->tokens[1][self::ROW_ID] ), ( $t_db_count == db_count_queries() )
			? 'Expired token #1 must be purged from cache.'
			: 'Expired token #1 must be purged from database.' );
		$t_db_count = db_count_queries();
		$this->assertFalse( token_exists( $this->tokens[2][self::ROW_ID] ), ( $t_db_count == db_count_queries() )
			? 'Expired token #2 must be purged from cache.'
			: 'Expired token #2 must be purged from database.' );
	}

	/**
	 * Tests token_purge_expired_once() 
	 * @return void
	 */
	public function testTokenPurgeExpiredOnce(): void {
		# Enable purging once
		global $g_tokens_purged;
		$g_tokens_purged = false;

		$this->assertTrue( token_exists( $this->tokens[1][self::ROW_ID] ), 'Expired token must exist.' );
		token_purge_expired_once();
		$this->assertFalse( token_exists( $this->tokens[1][self::ROW_ID] ), 'Expired token must be purged.' );

		# Only once
		$t_db_count = db_count_queries();
		token_purge_expired_once();
		$this->assertTrue( $t_db_count == db_count_queries(), 'The purge must occur only once.' );
	}

	/**
	 * Tests token_delete()
	 * @return void
	 */
	public function testTokenDelete(): void {
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The token must exist.' );
		
		token_delete( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );

		$t_db_count = db_count_queries();
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count < db_count_queries(), 'The token must be deleted from the cache.' );
		$this->assertNull( $t_token, 'The token should have been deleted from the database.' );
	}

	/**
	 * Tests token_delete_by_owner()
	 * @return void
	 */
	public function testTokenDeleteByOwner(): void {
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The token must exist.' );
		
		token_delete_by_owner( $this->tokens[0][self::ROW_OWNER] );

		$t_db_count = db_count_queries();
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count < db_count_queries(), 'The token should have been deleted from the cache.' );
		$this->assertNull( $t_token, 'The token should have been deleted from the database.' );
	}

	/**
	 * Tests token_delete_by_type()
	 * @return void
	 */
	public function testTokenDeleteByType(): void {
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The token must exist.' );
		
		token_delete_by_type( $this->tokens[0][self::ROW_TYPE] );

		$t_db_count = db_count_queries();
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count < db_count_queries(), 'The token should have been deleted from the cache.' );
		$this->assertNull( $t_token, 'The token should have been deleted from the database.' );
	}

	/**
	 * Tests token_update() with existing token
	 * @return void
	 */
	public function testTokenUpdateExisting(): void {
		# load cache
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The token must exist.' );

		$t_expiry = db_now() + TOKEN_EXPIRY_LAST_VISITED;
		$this->assertTrue( token_update( $t_token['id'], 'updated', TOKEN_EXPIRY_LAST_VISITED ) );

		# cache result
		$t_db_count = db_count_queries();
		$t_updated_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count == db_count_queries(), 'The token must be updated in the cache.' );
		$this->assertEquals( 'updated', $t_updated_token['value'], 'The token value must be updated in the cache.' );
		$this->assertTrue( $t_updated_token['expiry'] >= $t_expiry, 'The token expiry must be updated in the cache.' );

		# clear cache
		global $g_cache_token;
		$g_cache_token = [];

		# database result
		$t_db_count = db_count_queries();
		$t_updated_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count < db_count_queries(), 'The token must be updated in the database.' );
		$this->assertEquals( 'updated', $t_updated_token['value'], 'The token value must be updated in the database.' );
		$this->assertTrue( $t_updated_token['expiry'] >= $t_expiry, 'The token expiry must be updated in the database.' );
	}

	/**
	 * Tests token_update() with non-existing token
	 * @return void
	 */
	public function testTokenUpdateNonExisting(): void {
		$this->expectException( \Mantis\Exceptions\ClientException::class );
		$this->expectExceptionCode( ERROR_TOKEN_NOT_FOUND );
		token_update( self::UNKNOWN, 'invalid' );
	}

	/**
	 * Tests token_touch() with existing token
	 * @return void
	 */
	public function testTokenTouchExisting(): void {
		# load cache
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );

		$t_expiry = db_now() + TOKEN_EXPIRY_LAST_VISITED;
		token_touch( $t_token['id'], TOKEN_EXPIRY_LAST_VISITED );

		# cache result
		$t_db_count = db_count_queries();
		$t_updated_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count == db_count_queries(), 'The token must be touched in the cache.' );
		$this->assertTrue( $t_updated_token['expiry'] >= $t_expiry, 'The token expiry must be touched in the cache.' );
		$this->assertEquals( $t_token['id'], $t_updated_token['id'], 'The token must be touched in the cache.' );

		# clear cache
		global $g_cache_token;
		$g_cache_token = [];

		# database result
		$t_db_count = db_count_queries();
		$t_updated_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertTrue( $t_db_count < db_count_queries(), 'The token must be touched in the database.' );
		$this->assertTrue( $t_updated_token['expiry'] >= $t_expiry, 'The token expiry must be touched in the database.' );
		$this->assertEquals( $t_token['id'], $t_updated_token['id'], 'The token must be touched in the database.' );
	}

	/**
	 * Tests token_touch() with non-existing token
	 * @return void
	 */
	public function testTokenTouchNonExisting(): void {
		$this->expectException( \Mantis\Exceptions\ClientException::class );
		$this->expectExceptionCode( ERROR_TOKEN_NOT_FOUND );
		token_touch( self::UNKNOWN );
	}

	/**
	 * Tests token_create()
	 * @return void
	 */
	public function testTokenCreate(): void {
		$this->assertNull( token_get( TOKEN_COLLAPSE, self::KNOWN_OWNER ), 'The token must not exist.' );

		$t_id = token_create( TOKEN_COLLAPSE, 'created', TOKEN_EXPIRY, self::KNOWN_OWNER );
		$this->assertIsNumeric( $t_id, 'The token must be created.' );

		$t_token = token_get( TOKEN_COLLAPSE, self::KNOWN_OWNER );
		$this->assertArrayHasKey( 'id', $t_token, 'The created token must exist.' );
		$this->assertEquals( $t_token['id'], $t_id, 'The created token must exist.' );
		$this->assertEquals( $t_token['value'], 'created', 'The created token value must be set.' );

		# Cleanup
		$this->tokens []= [ $t_id ];
	}

	/**
	 * Tests token_set() with existing token
	 * @return void
	 */
	public function testTokenSetExisting(): void {
		# Update old
		$t_expiry = db_now() + TOKEN_EXPIRY_LAST_VISITED;
		$t_id = token_set( $this->tokens[0][self::ROW_TYPE], 'set', TOKEN_EXPIRY_LAST_VISITED, $this->tokens[0][self::ROW_OWNER] );
		$this->assertEquals( $t_id, $this->tokens[0][self::ROW_ID], 'The updated token must exist.' );

		# Ensure updated
		$t_token = token_get( $this->tokens[0][self::ROW_TYPE], $this->tokens[0][self::ROW_OWNER] );
		$this->assertArrayHasKey( 'id', $t_token, 'The updated token must exist.' );
		$this->assertEquals( $t_token['id'], $t_id, 'The updated token must exist.' );
		$this->assertEquals( $t_token['value'], 'set', 'The token value must be updated.' );
		$this->assertTrue( $t_token['expiry'] >= $t_expiry, 'The token expiry must be updated.' );
	}

	/**
	 * Tests token_set() with non-existing token
	 * @return void
	 */
	public function testTokenSetNonExisting(): void {
		$this->assertNull( token_get( TOKEN_COLLAPSE, self::KNOWN_OWNER ), 'The token must not exist.' );

		# Create new
		$t_id = token_set( TOKEN_COLLAPSE, 'set', TOKEN_EXPIRY, self::KNOWN_OWNER );
		$this->assertIsNumeric( $t_id, 'The token must be created.' );

		# Ensure Created
		$t_token = token_get( TOKEN_COLLAPSE, self::KNOWN_OWNER );
		$this->assertArrayHasKey( 'id', $t_token, 'The created token must exist.' );
		$this->assertEquals( $t_token['id'], $t_id, 'The created token must exist.' );
		$this->assertEquals( $t_token['value'], 'set', 'The created token value must be set.' );

		# Cleanup
		$this->tokens []= [ $t_id ];
	}
}
