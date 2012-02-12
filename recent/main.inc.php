<?php
/**
* This file holds data for getting recent votes.
* 
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* This function gets recent votes that were cast.
*
* @return string HTML of our recent votes
*/
function fivestarstats_recent() {

	$votes = fivestarstats_recent_votes();
	//fivestarstats_debug($votes); // Debugging

	$retval = fivestarstats_recent_get_html($votes);
	//fivestarstats_debug($retval); // Debugging

	return($retval);

} // End of fivestarstats_get_recent_votes()


/**
* Get our recent votes.
*
* @return array Array of votes cast.
*/
function fivestarstats_recent_votes() {

	$retval = array();

	$num = 20;
	$query = "SELECT "
		. "votes.timestamp, "
		. "votes.vote_source, "
		. "votes.content_type, votes.content_id, "
		. "votes.value, "
		. "users.uid, users.name, "
		. "node.nid, "
		. "node.title AS node_title, "
		. "comments.cid, comments.nid AS comments_nid, "
		. "comments.subject AS comment_title "
		. "FROM votingapi_vote AS votes "
		. "JOIN users ON votes.uid = users.uid "
		. "LEFT JOIN node ON (node.nid = votes.content_id AND votes.content_type='node') "
		. "LEFT JOIN comments ON (comments.cid = votes.content_id AND votes.content_type='comment') "
		. "WHERE "
		. "(content_type='node' OR content_type='comment') "
		. "AND value_type='percent' "
		. "ORDER BY vote_id DESC "
		;

	$cursor = pager_query($query, $num);
	while ($row = db_fetch_array($cursor)) {
		$retval[] = $row;
	}

	//fivestarstats_debug($retval); // Debugging
	return($retval);

} // End of givestarstats_recent_votes()


/**
* Turn our recent votes into HTML.
*
* @param array $data Our recent votes.
*
* @return string HTML 
*/
function fivestarstats_recent_get_html($data) {

	$retval = "";

	$header = array("Date", "Title", "User", "IP", "Rating");
    $rows = array();

    foreach ($data as $key => $value) {

		$date_string = format_date(
			$value["timestamp"], "custom", "Y-m-d H:i:s a");
		$value["rating"] = $value["value"] / 20;

		$maxlen = 60;

		if (!empty($value["comment_title"])) {
			$title = $value["comment_title"];
			$title = truncate_utf8($title, $maxlen);
			$nid = $value["comments_nid"];
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
			$user = t("Anonymous");

		}

		$ip = $value["vote_source"];
		$ip_string = l($ip, "admin/settings/fivestarstats/ip/" . $ip);

		$row = array();
		$row[] = array("data" => $date_string, "align" => "right",
			"style" => "white-space: nowrap; ",
			);
		$row[] = $title;
		$row[] = $user;
		$row[] = $ip_string;
		$row[] = $value["rating"] . t(" stars");
		$rows[] = $row;

	}


	$pager = theme("pager");

	$retval .= $pager;
	$retval .= theme("table", $header, $rows);
	$retval .= $pager;

	return($retval);

} // End of fivestarstats_recent_get_html()



