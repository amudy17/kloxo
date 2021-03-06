<?php
	$d1names = $domains;

	$tpath = "/opt/configs/nsd/conf/master";
	$d2files = glob("{$tpath}/*");

	if (empty($d2files)) { return; }

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
		$zone  = "zone:\n    name: {$v}\n    zonefile: master/{$v}\n";

		$zone .= "    include: \"/opt/configs/nsd/conf/defaults/nsd.acl.conf\"\n";

		$str .= $zone . "\n";
	}

	$file = "/opt/configs/nsd/conf/defaults/nsd.master.conf";

	file_put_contents($file, $str);

	if (!file_exists("/etc/rc.d/init.id/nsd")) { return; }

	if (file_exists("/etc/rc.d/init.d/nsd")) {
		if (file_exists("/usr/sbin/nsd-control")) {
			$n = "/usr/sbin/nsd-control";
			exec_with_all_closed("{$n} write ; {$n} reload; {$n} notify");
		} else {
			$n = "/usr/sbin/nsdc";
			exec_with_all_closed("{$n} rebuild ; {$n} reload; {$n} notify");
		}
	}

//	createRestartFile("restart-dns");
