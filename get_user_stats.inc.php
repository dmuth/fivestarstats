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
	// For now, this is hard coded.  I'll probably change this later.
	//
	$min_votes = 10;

	$retval["top_rated"] = fivestarstats_get_user_stats_top_rated($num, $min_votes);
	$retval["bottom_rated"] = fivestarstats_get_user_stats_bottom_rated($num, $min_votes);
	$retval["most_1_star_votes"] = fivestarstats_get_user_stats_most_1_star_votes($num);

	//
	// Get top posters. Both nodes AND comments.
	//
	$retval["top_posters"] = fivestarstats_get_user_stats_top_posters($num);

	foreach ($retval["top_posters"] as $key => $value) {

		$uid = $value["uid"];
		$tmp = fivestarstats_get_user_stats_avg_rating($uid);
		foreach ($tmp as $key2 => $value2) {
			$retval["top_posters"][$key][$key2] = $value2;
		}

		$retval["top_posters"][$key]["num_stars"] = fivestarstats_get_user_stats_ratings($uid);

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
		. "("
			. "SELECT uid, count(*) AS cnt FROM comments GROUP BY uid "
			. "UNION ALL "
			. "SELECT uid, count(*) AS cnt FROM node GROUP BY uid"
			. ")"
		. " tbl1 "
		. "JOIN users ON tbl1.uid = users.uid GROUP BY uid ORDER BY cnt DESC "
		. "LIMIT $num"
		;
	$cursor = db_query($query);
	
	while ($row = db_fetch_array($cursor)) {

		$user = array();
		$user["num_posts"] = $row["cnt"];
		$user["name"] = $row["name"];
		$user["uid"] = $row["uid"];

		$retval[] = $user;

	}

	return($retval);

} // End of fivestarstats_get_user_stats_top_posters()


/**
* Get the average rating for a specific user's posts/comments.
*
* @param integer $uid the User ID we want an average rating for.
*
* @return array Associative array of data.
*/
function fivestarstats_get_user_stats_avg_rating($uid) {

	$retval = array();

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
		. "UNION ALL "
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

	$retval["num_votes"] = $row["cnt"];
	$retval["total"] = $row["total"];
	$retval["average"] = $row["total"] / $row["cnt"] / 20;

	return($retval);

} // End of fivestarstats_get_user_stats_avg_rating()


/**
* Get a breakdown of ratings by star number for a specific user.
*
* @param integer $uid the User ID we want an average rating for.
*
* @return array Associative array of data.
*/
function fivestarstats_get_user_stats_ratings($uid) {

	$retval = array();

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
	while ($row = db_fetch_array($cursor)) {
		$num_stars = $row["value"] / 20;
		$retval[$num_stars] = $row["cnt"];
	}

	//
	// Put the highest ratings first.
	//
	krsort($retval);

	return($retval);

} // End of fivestarstats_get_user_stats_avg_rating()


/**
* Determine our top-rated users with a minimum number of votes.
*
* @param integer $num The number of top users we want
*
* @param integer $min_votes The minimum number of votes for each user.
*	This keeps new users with a single vote from being pushed to the
*	top of the list.
*
* @return array An array of top users.
*/
function fivestarstats_get_user_stats_top_rated($num, $min_votes) {

	$retval = array();

	$query = "SELECT "
		. "tbl1.uid, users.name, "
		. "SUM(value) AS total, COUNT(*) AS num_votes, AVG(value) AS average "
		. "FROM ("
			. "SELECT comments.uid AS uid, "
			. "value "
			. "FROM votingapi_vote "
			. "JOIN comments ON comments.cid = content_id "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='comment' "

			. "UNION ALL "

			. "SELECT node.uid AS uid, "
			. "value "
			. "FROM votingapi_vote "
			. "JOIN node ON node.nid = content_id "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='node' "
			. ") tbl1 "

		. "JOIN users ON users.uid = tbl1.uid "
		. "GROUP BY uid "
		. "HAVING num_votes >= %d "
		. "ORDER BY average DESC, num_votes DESC "
		. "LIMIT %d "
		;

	$query_args = array($min_votes, $num);
	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		unset($row["total"]);
		$row["average"] = $row["average"] / 20;
		$retval[] = $row;
	}

	return($retval);

} // End of fivestarstats_get_user_stats_top_rated()


/**
* Determine our bottom-rated users with a minimum number of votes.
*
* @param integer $num The number of top users we want
*
* @param integer $min_votes The minimum number of votes for each user.
*	This keeps new users with a single vote from being pushed to the
*	top of the list.
*
* @return array An array of top users.
*/
function fivestarstats_get_user_stats_bottom_rated($num, $min_votes) {

	$retval = array();

	$query = "SELECT "
		. "tbl1.uid, users.name, "
		. "SUM(value) AS total, COUNT(*) AS num_votes, AVG(value) AS average "
		. "FROM ("
			. "SELECT comments.uid AS uid, "
			. "value "
			. "FROM votingapi_vote "
			. "JOIN comments ON comments.cid = content_id "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='comment' "

			. "UNION ALL "

			. "SELECT node.uid AS uid, "
			. "value "
			. "FROM votingapi_vote "
			. "JOIN node ON node.nid = content_id "
			. "WHERE "
			. "value_type='percent' "
			. "AND content_type='node' "
			. ") tbl1 "

		. "JOIN users ON users.uid = tbl1.uid "
		. "GROUP BY uid "
		. "HAVING num_votes >= %d "
		. "ORDER BY average ASC, num_votes DESC "
		. "LIMIT %d "
		;

	$query_args = array($min_votes, $num);
	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		unset($row["total"]);
		$row["average"] = $row["average"] / 20;
		$retval[] = $row;
	}

	return($retval);

} // End of fivestarstats_get_user_stats_bottom_rated()


/**
* Determine which users have the most 1-star votes against them. 
* This can be used to catch abuse of the voting system.
*
* @param integer $num The number of users we want.
*/
function fivestarstats_get_user_stats_most_1_star_votes($num) {

	$retval = array();

	$query = "SELECT "
		. "tbl1.uid, users.name, COUNT(*) AS cnt "
		. "FROM ("
			. "SELECT node.uid "
			. "FROM votingapi_vote "
			. "JOIN node ON node.nid=content_id "
			. "WHERE "
			. "value_type='percent' "
			. "AND value=20 "
			. "AND content_type='node' "

			. "UNION ALL "

			. "SELECT comments.uid "
			. "FROM votingapi_vote "
			. "JOIN comments ON comments.cid=content_id "
			. "WHERE "
			. "value_type='percent' "
			. "AND value=20 "
			. "AND content_type='comment' "
		. ") tbl1 "
		. "JOIN users ON users.uid = tbl1.uid "
		. "GROUP BY tbl1.uid "
		. "ORDER BY cnt DESC "
		. "LIMIT $num "
		;

	$cursor = db_query($query);
	while ($row = db_fetch_array($cursor)) {
		$retval[] = $row;
	}

	return($retval);

} // End of fivestarstats_get_user_stats_most_1_star_votes()

