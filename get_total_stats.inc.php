<?php
/**
* This file holds functions that relate to getting total statistics on votes.
*
* @author Douglas Muth <http://www.dmuth.org/">
*/


/**
* Get total statistics.
*
* @return array An associative array of data.
*/
function fivestarstats_get_total_stats() {

	//
	// How many votes cast?
	//
	$retval = array();
	$query = "SELECT COUNT(*) AS cnt FROM votingapi_vote WHERE value_type='percent'";
	$cursor = db_query($query);
	$row = db_fetch_array($cursor);
	$retval["num_votes"] = $row["cnt"];
	
	//
	// How many unique IPs voted?
	//
	$query = "SELECT COUNT(*) AS cnt FROM ("
		. "SELECT DISTINCT vote_source FROM votingapi_vote "
			. "WHERE value_type='perent') AS tbl1";
	$cursor = db_query($query);
	$row = db_fetch_array($cursor);
	$retval["num_ips"] = $row["cnt"];

	//
	// What's the average vote value in stars?
	//
	$query = "SELECT AVG(value) AS average FROM votingapi_vote "
		. "WHERE value_type='percent'";
	$cursor = db_query($query);
	$row = db_fetch_array($cursor);
	$average = $row["average"];
	$retval["average_stars"] = sprintf("%.2f", $average / 20);

	return($retval);

} // End of fivestarstats_get_total_stats()


