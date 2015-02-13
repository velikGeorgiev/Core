<?php

return array(
	"routes" => array(
		"/" => array(
			"route" => "",
			"controller" => "index",
			"action" => "index"
		),

		"info" => array(
			"route" => "info/[action]/:data",
			"controller" => "index",
			"action" => "*"
		)
	),

	"default" => array(
		"controller" => "index",
		"action" => "index"
	)
);

?>