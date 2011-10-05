<?php
/* 
 * Example to remove a file/dir
 * as the call is the same for file or dir, we
 * only need to check it exists
 * 
 * uses php svn module:
 * http://php.net/manual/en/book.svn.php 
 */

$svn_path = "/var/lib/svn/repos/testrepo";
$path = "/test/myfile.txt";

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

//check if is either a file or dir, if we
//are interested in a single type, then
//remove any of the checking
if (svn_fs_is_file($repo_root, $path)
	|| svn_fs_is_dir($repo_root, $path)) {
	//remove the object and do the commit	
	svn_fs_delete($repo_root, $path);
	svn_repos_fs_commit_txn($repo_txn);
} else  {
	//file not found, so abort transaction
	svn_fs_abort_txn ( $repo_txn );
}		

