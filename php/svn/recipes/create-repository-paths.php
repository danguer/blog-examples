<?php
/* 
 * Example to create common paths in an empty repository
 * uses php svn module:
 * http://php.net/manual/en/book.svn.php 
 */

//the path to your repository 
$svn_path = "/var/lib/svn/repos/testrepo";

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

//create the dirs
svn_fs_make_dir($repo_root, "trunk");
svn_fs_make_dir($repo_root, "tags");
svn_fs_make_dir($repo_root, "branches");
		
//finally commit the transaction
svn_repos_fs_commit_txn($repo_txn);