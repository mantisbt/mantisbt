<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: relationship_api.php,v 1.7 2004-04-08 18:04:53 prescience Exp $
	# --------------------------------------------------------

	### Relationship API ###

	# --------------------
	function relationship_add( $p_src_bug_id, $p_dest_bug_id, $p_relationship_type ) {
		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "INSERT INTO $t_mantis_bug_relationship_table
				( id, source_bug_id, destination_bug_id, relationship_type )
				VALUES
				( null, '$p_src_bug_id', '$p_dest_bug_id', '$p_relationship_type' )";
		return db_query( $query );
	}
	# --------------------
	function relationship_update( $p_relation_id, $p_src_bug_id, $p_dest_bug_id, $p_relationship_type ) {
		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "UPDATE $t_mantis_bug_relationship_table
				SET source_bug_id='$p_src_bug_id',
					destination_bug_id='$p_dest_bug_id',
					relationship_type='$p_relationship_type'
				WHERE id='$p_relation_id'";
		return db_query( $query );
	}
	# --------------------
	function relationship_delete( $p_relation_id ) {
		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "DELETE FROM $t_mantis_bug_relationship_table
				WHERE id='$p_relation_id'";
		return db_query( $query );
	}
	# --------------------
	function relationship_fetch( $p_relation_id ) {
		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE id='$p_relation_id'";
		$result = db_query( $query );
		return db_fetch_array( $result );
	}
	# --------------------
	function relationship_fetch_all_src( $p_src_bug_id ) {
		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE source_bug_id='$p_src_bug_id'
				ORDER BY relationship_type";
		return db_query( $query );
	}
	# --------------------
	function relationship_fetch_all_dest( $p_src_bug_id ) {
		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE destination_bug_id='$p_src_bug_id'
				ORDER BY relationship_type";
		return db_query( $query );
	}
	# --------------------
?>