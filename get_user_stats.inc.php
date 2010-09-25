<?php
/**
* This file holds functions that relate to getting user vote statistics.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* Get statistics on users.
*
* @param integer $num The number of "top" users to query.
*
* @return array Associative array of data where the key is the user ID.
*/
function fivestarstats_get_user_stats($num) {

	$retval = array();

	//
	// Get top posters. Both nodes AND comments.
	//
	$retval = fivestarstats_get_user_stats_top_posters($num);

	foreach ($retval as $key => $value) {

		$uid = $key;

		//
		// Get average rating for each user's posts and comments.
		//
		$query = ""
			. "SELECT SUM(cnt) AS cnt, SUM(total) AS total FROM "
			. "("
			. "SELECT COUNT(*) AS cnt, SUM(value) AS total FROM votingapi_vote "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='node' AND content_id IN "
				. "(SELECT nid FROM node WHERE uid=%d) "
			. "UNION "
			. "SELECT COUNT(*) AS cnt, SUM(value) AS total FROM votingapi_vote "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='comment' AND content_id IN "
				. "(SELECT cid FROM comments WHERE uid=%d) "
			. ") tbl1 "
			;
		$query_args = array($uid, $uid);
		$cursor = db_query($query, $query_args);
		$row = db_fetch_array($cursor);

		$retval[$uid]["num_votes"] = $row["cnt"];
		$retval[$uid]["total"] = $row["total"];
		$retval[$uid]["average"] = $row["total"] / $row["cnt"] / 20;

		//
		// Get a breakdown of ratings for each user.
		//
		$query = ""
			. "SELECT value, sum(cnt) AS cnt FROM "
			. "("
			. "SELECT 'node' AS type, value, COUNT(*) AS cnt "
			. "FROM votingapi_vote "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='node' AND content_id IN "
				. "(SELECT nid FROM node WHERE uid=%d) "
			. "GROUP BY value "
			. "UNION "
			. "SELECT 'comment' AS type, value, COUNT(*) AS cnt "
			. "FROM votingapi_vote "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='comment' AND content_id IN "
				. "(SELECT cid FROM comments WHERE uid=%d) "
			. "GROUP BY value "
			. ") tbl1 "
			. "GROUP BY value "
			;
		$query_args = array($uid, $uid);
		$cursor = db_query($query, $query_args);
		$retval[$uid]["num_stars"] = array();
		while ($row = db_fetch_array($cursor)) {
			$num_stars = $row["value"] / 20;
			$retval[$uid]["num_stars"][$num_stars] = $row["cnt"];
		}

	}

	return($retval);

} // End of fivestarstats_get_user_stats()


/**
* Get top posters by number of posts/comments.
*
* @param integer $num The number of "top" users to query.
*
* @return array Associative array of data where the key is the user ID.
*/
function fivestarstats_get_user_stats_top_posters($num) {

	$retval = array();

	$query = "SELECT tbl1.uid, sum(cnt) AS cnt, users.name FROM "
		. "(SELECT uid, count(*) AS cnt FROM comments GROUP BY uid "
			. "UNION SELECT uid, count(*) AS cnt FROM node GROUP BY uid)"
			. " tbl1 "
		. "JOIN users ON tbl1.uid = users.uid GROUP BY uid ORDER BY cnt DESC "
		. "LIMIT $num"
		;
	$cursor = db_query($query);
	
	while ($row = db_fetch_array($cursor)) {

		$uid = $row["uid"];
		$user = array();
		$user["num_posts"] = $row["cnt"];
		$user["name"] = $row["name"];

		$retval[$uid] = $user;

	}

	return($retval);

} // End of fivestarstats_get_user_stats_top_posters()
