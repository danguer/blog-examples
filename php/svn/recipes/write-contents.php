<?php
/* 
 * Example to add content to a file
 * uses php svn module:
 * http://php.net/manual/en/book.svn.php 
 */

function makeSubdirs($repo_root, $dir) {
	//check if the dir is already an dir,
	//if so, then skip the action
	if (!svn_fs_is_dir($repo_root, $dir)) {
		//check if the parent is created
		$parent_dir = dirname($dir);
		
		//create parents recursive,
		//the root dir will show as "." 
		//so skip when reach it
		if ($parent_dir != ".")
			makeSubdirs($repo_root, $parent_dir);
			
		//finally create the dir after all parent's are done
		svn_fs_make_dir($repo_root, $dir);
	}
}

$svn_path = "/var/lib/svn/repos/testrepo";
$path = "/test/myfile.txt";
$data = "Lorem Ipsum, file generated with svn";

//open the repo
$svn_repo = svn_repos_open($svn_path);

//get a fs handler
$repo_fs = svn_repos_fs($svn_repo);

//get latest revision
$latest_revision = svn_fs_youngest_rev($repo_fs);

//generate a transaction, with user and log message
$repo_txn = svn_repos_fs_begin_txn_for_commit($svn_repo, $latest_revision, 'user-repo-label', 'Creating paths');

//get the root respource to this transaction
$repo_root = svn_fs_txn_root($repo_txn);

//check if exists, if not create the file
if (!svn_fs_is_file($repo_root, $path)) {
	makeSubdirs($repo_root, dirname($path));
	
	//this will create an empty file in svn
	svn_fs_make_file($repo_root, $path);
}

//get stream to write data
$fp = svn_fs_apply_text($repo_root, $path);
fwrite($fp, $data);
fclose($fp); //important to close, if not the transaction will fail

//finally commit the transaction
svn_repos_fs_commit_txn($repo_txn);