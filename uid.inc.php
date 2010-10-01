<?php
/**
* This file holds functions that tell us what votes were cast on 
* specific users.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* This function gets a summary of votes that were cast on this
* user's posts.
*
* @param integer $uid The user ID
*
* @return array An array where the key is the number of stars and
*	the value is the number of votes.
*/
function fivestarstats_uid_received_votes($uid) {

	$retval = array();

	$query = "SELECT value, COUNT(*) AS cnt "
		. "FROM "
		. "votingapi_vote "
		. "WHERE "
		. "value_type='percent' "
		. "AND "
			. "("
			. "(content_type='node' AND content_id IN (SELECT nid FROM node WHERE uid=%d)) "
			. "OR "
			. "(content_type='comment' AND content_id IN (SELECT cid FROM comments WHERE uid=%d)) "
			. ")"
		. "GROUP BY value "
		. "ORDER BY value DESC "
		;
	$query_args = array($uid, $uid);
	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		$num_stars = $row["value"] / 20;
		$cnt = $row["cnt"];
		$retval[$num_stars] = $cnt;
	}

	return($retval);

} // End of fivestarstats_uid_received_votes()


