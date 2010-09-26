<?php
/**
* Our form-related code.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* Our form callback.
*
* @param array $form_data Form data
*
* @param array $data Our array of data that we passed in
*
* @return array An array of form elements for the form generator.
*/
function fivestarstats_form($form_data, $data) {

	$retval = array();

	$retval["total_votes"] = fivestarstats_form_total_votes($data);

	return($retval);

} // End of fivestarstats_form()


/**
* Create form elements for the total vote data.
*
* @param array $data Our array of data that we passed in
*
* @return array An array of form elements for the form generator.
*/
function fivestarstats_form_total_votes($data) {

	$retval = array();

	$retval = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#title" => t("Total Votes"),
		);

	$retval["content"] = array(
		"#type" => "item",
		"#value" => t("%num_votes from %num_ips unique IPs "
			. "with average of %num_stars stars per vote.",
			array("%num_votes" => $data["total"]["num_votes"],
				"%num_ips" => $data["total"]["num_ips"],
				"%num_stars" => $data["total"]["average_stars"])
			)
		);

	return($retval);

} // End of fivestarstats_form_total_values()

