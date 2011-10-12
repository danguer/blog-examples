<?php
/*
Copyright (c) 2011, Daniel Guerrero
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the Daniel Guerrero nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL DANIEL GUERRERO BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* 
 * Example to create a dir in recursive way
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

//the path to your repository 
$svn_path = "/var/lib/svn/repos/testrepo";
$dir = "/test/subdir1/subdir2/subdir3";

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
makeSubdirs($repo_root, $dir);	
	
//finally commit the transaction
svn_repos_fs_commit_txn($repo_txn);
