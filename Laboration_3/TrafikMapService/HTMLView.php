<?php

class HTMLView
{
	public function __construct() {
		date_default_timezone_set('Europe/Stockholm');
	}

	public function render() { // Note: Add traffic messages as argument?
	echo "
		<!DOCTYPE html>
		<html lang='sv'>
			<head>
				<meta charset='UTF-8'>
				<title>dt222cc - Laboration 3</title>
				<link rel='stylesheet' href='css/design.css' />
			</head>

			<body>
				<div id='container'>
					<div id='my-list-container'>
						Välj kategori:
						<select id='filter'></select>
						<ul id='list'>
							<li>1</li>
							<li>2</li>
							<li>3</li>
							<li>4</li>
						</ul>
					</div>
				</div>
			</body>
		</html>
		";
	}
}