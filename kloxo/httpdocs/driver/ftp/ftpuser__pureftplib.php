<?php 

class ftpuser__pureftp extends lxDriverClass
{
	function dbactionAdd()
	{
		global $gbl, $sgbl, $login, $ghtml; 

		$dir = $this->main->__var_full_directory;
		$dir = expand_real_root($dir);
		$pass = $this->main->realpass;

		if (!lxfile_exists($dir)) {
			lxfile_mkdir($dir);
			lxfile_unix_chown($dir, $this->main->__var_username);
		}

		if (!$pass) { $pass = randomString(8); }

		lxshell_input("$pass\n$pass\n", "pure-pw", "useradd",  $this->main->nname, "-u", $this->main->__var_username, "-d",  $dir, "-m");

		$this->setQuota();

		// If the user is added is fully formed, this makes sure that all his properties are synced.
		$this->toggleStatus();
	}

	// MR -- function to combine add + quota + status without '-m' (create .pdb) - using by fixftpuser
	function setFix()
	{
		global $gbl, $sgbl, $login, $ghtml; 

		$dir = $this->main->__var_full_directory;
		$dir = expand_real_root($dir);
		$pass = $this->main->realpass;

		$nname = $this->main->nname;
		$username = $this->main->__var_username;

		if (!lxfile_exists($dir)) {
			lxfile_mkdir($dir);
			lxfile_unix_chown($dir, $username);
		}

		if (!$pass) { $pass = randomString(8); }

//		lxshell_input("$pass\n$pass\n", "pure-pw", "useradd",  $nname, "-u", $username, "-d",  $dir);

		if ($this->main->ftp_disk_usage > 0) {
			$q  = "-N {$this->main->ftp_disk_usage}";
			$q2 = $this->main->ftp_disk_usage;
		} else {
			$q = "-N ''";
			$q2 = "";
		}

		if ($this->main->isOn('status')) {
			$z = "-z 0000-2359";
			$z2 = "0000-2359";
		} else {
			$z = "-z 0000-0000";
			$z2 = "0000-0000";
		}

//		exec("pure-pw usermod {$nname} {$q} {z}");

		lxshell_input("$pass\n$pass\n", "pure-pw", "useradd",  $nname, "-u", $username, "-d",  $dir, "-N", $q2, "-z", $z2);
	}

	function dbactionDelete()
	{
		global $gbl, $sgbl, $login, $ghtml; 

	//	$command =  "pure-pw userdel " . $this->main->nname . " -f /etc/pureftpd.passwd -m";

	//	dprint($command); 
	//	shell_exec($command);

		$u = $this->main->__var_username;

		$d = str_replace("/home/{$u}/", "", $this->main->__var_full_directory);

	//	$c = db_get_count("web", "nname = '{$d}'");
		$c = db_get_count("web", "customer_name = '{$u}' AND docroot = '{$d}'");

		if ((int)$c !== 0) {
			throw new lxException($login->getThrow("no_permit_to_delete_main_ftpuser"), '', $this->main->nname);
		}

		lxshell_return("pure-pw", "userdel", $this->main->nname, "-m");
	}

	function toggleStatus()
	{
		if ($this->main->isOn('status')) {
			lxshell_return("pure-pw", "usermod", $this->main->nname, "-z", "0000-2359", "-m");
		} else {
			lxshell_return("pure-pw", "usermod", $this->main->nname, "-z", "0000-0000", "-m");
		}
	}

	function setQuota()
	{
		if ($this->main->ftp_disk_usage > 0) {
			lxshell_return("pure-pw", "usermod", $this->main->nname, "-N", $this->main->ftp_disk_usage, "-m");
		} else {
			// This is because the shell_return cannot send '' to the program.
			$cmd = "pure-pw usermod {$this->main->nname} -N '' -m";
			log_log("shell_exec", $cmd);
			system($cmd);
		//	lxshell_return("pure-pw", "usermod", $this->main->nname, "-N", "", "-m");
		}
	}

	function dbactionUpdate($subaction)
	{
		global $gbl, $sgbl, $login, $ghtml; 

		$dir = $this->main->__var_full_directory;
		$dir = expand_real_root($dir);

		switch($subaction) {
			case "full_update":
				$pass = $this->main->realpass;
				lxshell_input("$pass\n$pass\n", "pure-pw", "passwd", $this->main->nname, "-m");
				lxshell_return("pure-pw", "usermod", $this->main->nname, "-d", $dir, "-m");
				$this->toggleStatus();
				$this->setQuota();
				break;

			case "password":
				$pass = $this->main->realpass;
				lxshell_input("$pass\n$pass\n", "pure-pw", "passwd", $this->main->nname, "-m");
				break;

			case "toggle_status":
				$this->toggleStatus();
				break;

			case "edit":
				lxshell_return("pure-pw", "usermod", $this->main->nname, "-d", $dir, "-m");
				$this->setQuota();
				break;

			case "changeowner":
				lxshell_return("pure-pw", "usermod", $this->main->nname, "-u", $this->main->__var_username, "-d", $dir, "-m");
				break;

			case "fix":
				$this->setFix();
				break;
		}
	}
}


