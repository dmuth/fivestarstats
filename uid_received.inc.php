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


/**
* Create HTML giving a breakdown of how many votes a specific user received
* by rating.
*
* @param integer $uid The user_id
*
* @param array $data The data structure of votes
*
* @return string HTML code
*/
function fivestarstats_uid_received_votes_html($uid, $data) {

	$retval = "";

	$header = array(t("Stars"), t("Number of votes received"));
	$rows = array();

	$num_votes = 0;
	foreach ($data as $key => $value) {

		$link = l($value . t(" votes received"), 
			"admin/settings/fivestarstats/uid/$uid/votes/$key");

		$row = array();
		$row[] = t("%num stars", array("%num" => $key));
		$row[] = array("data" => $link, "align" => "right");
		$rows[] = $row;
		$num_votes += $value;
	}

	$rows[] = array(
		t("All Votes"),
		array("data" => l(t("!num_votes votes received", array("!num_votes" => $num_votes)),
			"admin/settings/fivestarstats/uid/$uid/votes/all"
			),
			"align" => "right"),
			);

	$retval .= theme("table", $header, $rows);

	return($retval);

} // End of fivestarstats_uid_received_votes_html()


/**
* Fetch details on what user posts received a specific number of stars.
*
* @param integer $uid The user_id
*
* @param mixed $num_stars The number of stars of votes, or "all" 
*	for all stars.
*
* @return array Array of posts with the specified number of stars received.
*/
function fivestarstats_uid_received_votes_detail_data($uid, $num_stars) {

	$retval = array();

	$query = "SELECT "
		. "votingapi_vote.uid, "
		. "users.name, "
		. "votingapi_vote.vote_source, "
		//
		// Get our NID for nodes and comments.
		//
		. "IF(node.nid, node.nid, comments.nid) AS nid, "
		. "node.title AS node_title, "
		. "comments.cid, "
		. "comments.subject AS comment_title, "
		. "votingapi_vote.value, votingapi_vote.timestamp "
		. "FROM votingapi_vote "
		. "LEFT JOIN users ON (users.uid=votingapi_vote.uid) "
		. "LEFT JOIN node ON (node.nid=votingapi_vote.content_id AND content_type='node') "
		. "LEFT JOIN comments ON (comments.cid=votingapi_vote.content_id AND content_type='comment') "
		. "WHERE "
		. "("
			. "(content_type='node' AND content_id IN (SELECT nid FROM node WHERE uid=%d)) "
			. "OR "
			. "(content_type='comment' AND content_id IN (SELECT cid FROM comments WHERE uid=%d)) "
		. ")"
		. "AND value_type='percent' "
		;
	$query_args = array($uid, $uid);
	if ($num_stars != "all") {
		$query .= "AND value=%d ";
		$query_args[] = $num_stars * 20;
	}

	$query .= "ORDER BY votingapi_vote.timestamp DESC ";

	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		$row["timestamp"] = format_date(
		$row["timestamp"], "custom", "Y-m-d H:h:s a");
		$row["rating"] = $row["value"] / 20;
		$retval[] = $row;
	}

	return($retval);

} // End of fivestarstats_uid_received_votes_detail_data()


/**
* Turn the data structure of votes that a user received into data.
*
* @param integer $uid The user_id
*
* @param object $user The user object for the user_id
*
* @param array $data Our array of vote data
*
* @return string HTML code.
*/
function fivestarstats_uid_received_votes_detail_html($uid, $user, $data) {

	$retval = "";

	$retval .= t("<h2>Votes cast on user: %user</h2>", 
		array("%user" => $user->name));

	$header = array(t("Date"), t("Title"), t("Voter"), t("Rating"));
	$rows = array();

	foreach ($data as $key => $value) {

		$maxlen = 60;

		if (!empty($value["comment_title"])) {
			$title = $value["comment_title"];
			$title = truncate_utf8($title, $maxlen);
			$nid = $value["nid"];
			$cid = $value["cid"];
			$options = array("fragment" => "comment-" . $cid);
			$title = l(t("Comment: ") . $title,
				"node/" . $nid, $options);

		} else {
			$title = $value["node_title"];
			$title = truncate_utf8($title, $maxlen);
			$nid = $value["nid"];
			$title = l($title, "node/" . $nid);

		}

		if (!empty($value["uid"])) {
			$user = l($value["name"], "user/" . $value["uid"]);

		} else {
			$link = l($value["vote_source"], 
				"admin/settings/fivestarstats/ip/" . $value["vote_source"]);
			$user = $link;

		}

		$row = array();
		$row[] = array("data" => $value["timestamp"], "align" => "right");
		$row[] = $title;
		$row[] = $user;
		$row[] = $value["rating"] . t(" stars");
		$rows[] = $row;

	}

	$retval .= theme("table", $header, $rows);

	return($retval);

} // End of fivestarstats_uid_received_votes_detail_html()


/**
* Get more of a "summary" view of ratings a user has received.
*
* @param integer $uid The user ID
*
* @return array An array where the key is the number of stars and
*	the value is the number of votes.
*/
function fivestarstats_uid_received_summary($uid) {

	$retval = array();

	$query = "SELECT COUNT(*) AS cnt, AVG(value) as avg "
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
		. "ORDER BY value DESC "
		;
	$query_args = array($uid, $uid);
	$cursor = db_query($query, $query_args);
	$row = db_fetch_array($cursor);
	$retval["num_votes_received"] = $row["cnt"];
	$retval["avg_rating"] = $row["avg"] / 20;

	return($retval);

} // End of fivestarstats_uid_received_summary()


/**
* Get HTML for votes cast on a specific user.
*
* @param integer $uid The user Id
*
* @return string HTML code.
*/
function fivestarstats_uid_received_summary_html($uid) {

	$retval = "";

	$data = fivestarstats_uid_received_summary($uid);
	//print_r($data); // Debugging

	$num_votes_received = $data["num_votes_received"];
	$num_stars_received = sprintf("%.1f", $data["avg_rating"]);
	$rating = $num_stars_received * 20;

	//
	// Get our stars with the proper rating filled in.
	//
	$star_display = theme("fivestar_static", $rating);

	//
	// Include details on ratings.
	//
	$title = t("On posts and comments");
	$text_display = t("Average: !num_stars_received (!num_votes_received votes)",
		array(
			"!num_stars_received" => $num_stars_received,
			"!num_votes_received" => $num_votes_received,
			)
		);

	//
	// Theme it all together!
	//
	$retval .= theme('fivestar_static_element', $star_display, $title, $text_display);

	return($retval);

} // End of fivestar_stats_uid_received_summary_html()


