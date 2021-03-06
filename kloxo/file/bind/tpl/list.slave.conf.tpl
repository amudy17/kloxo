<?php
	foreach($domains as $k => $v) {
		$t = explode(':', $v);

		$d1names[] = $t[0];
		$d1ips[] = $t[1];
	}

	$tpath = "/opt/configs/bind/conf/slave";

	$d2files = glob("{$tpath}/*");

	foreach ($d2files as $k => $v) {
		$d2names[] = str_replace("{$tpath}/", '', $v);
	}

	$d2olds = array_diff($d2names, $d1names);

	// MR -- delete unwanted files
	if (!empty($d2olds)) {
		foreach ($d2olds as $k => $v) {
			unlink("{$tpath}/{$v}");
		}
	}

	$str = '';

	foreach ($d1names as $k => $v) {
		$c = $d1ips[$k];
		
		$zone  = "zone \"{$v}\" {";
		$zone .= "\n    type slave;";
		$zone .= "\n    file \"slave/{$v}\";";
		$zone .= "\n    masters { {$c}; };";
		$zone .= "\n    masterfile-format text;";
		$zone .= "\n};\n\n";

		$str .= $zone;
	}

	$file = "/opt/configs/bind/conf/defaults/named.slave.conf";

	file_put_contents($file, $str);

	if (!file_exists("/etc/rc.d/init.id/named")) { return; }

//	createRestartFile("restart-dns");
	exec_with_all_closed("rndc reload");

