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



