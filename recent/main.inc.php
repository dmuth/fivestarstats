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
fivestarstats_debug($votes);

	
/**
TODO:
- Put into a nice table
	- add links
- Add paging
*/

} // End of fivestarstats_get_recent_votes()


/**
* Get our recent votes.
*
* @return array Array of votes cast.
*/
function fivestarstats_recent_votes() {

	$retval = array();

	$num = 5;
	$query = "SELECT "
		. "votes.*, users.name, "
		. "node.title AS node_title, "
		. "comments.cid, "
		. "comments.subject AS comment_title "
		. "FROM votingapi_vote AS votes "
		. "JOIN users ON votes.uid = users.uid "
		. "LEFT JOIN node ON (node.nid = votes.content_id AND votes.content_type='node') "
		. "LEFT JOIN comments ON (comments.cid = votes.content_id AND votes.content_type='comment') "
		. "WHERE "
		. "(content_type='node' OR content_type='comment') "
		. "AND value_type='percent' "
		. "ORDER BY vote_id DESC "
		. "LIMIT $num "
		;

	$cursor = db_query($query);
	while ($row = db_fetch_array($cursor)) {
		$retval[] = $row;
	}

	return($retval);

} // End of givestarstats_recent_votes()


