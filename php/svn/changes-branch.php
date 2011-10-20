<?php 
/* 
Copyright (c) 2010, Daniel Guerrero 
All rights reserved. 

Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions are met: 
    * Redistributions of source code must retain the above copyright 
      notice, this list of conditions and the following disclaimer. 
    * Redistributions in binary form must reproduce the above copyright 
      notice, this list of conditions and the following disclaimer in the 
      documentation and/or other materials provided with the distribution. 
    * Neither the name of the LiveSourcing nor the 
      names of its contributors may be used to endorse or promote products 
      derived from this software without specific prior written permission. 

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY 
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND 
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
 */ 

/* 
 * Scripts that checks changes across branches and trunk
 * 
 * Usage: 
 * changes-branch.php http://svn.example.com myproject branch-123
 * 
 * It uses standard project paths (trunk, tags, branches) if you have other 
 * layout, then you will need to change values in line 76  
 */ 


function svn_data($cmd, $basepath) { 
    $output = array(); 
    exec($cmd, $output); 
    $matches = array(); 
    $revs = array(); 
    $files = array(); 
    $rev = null; 
     
    foreach($output as $line) { 
        $pattern = '|r([0-9]+)|i';     
        preg_match_all($pattern, $line, $matches); 
             
        if (isset($matches[1][0])) { 
            $rev = $matches[1][0]; 
            $revs[] = $rev; 
        } 
         
        //check the file changed 
        $pos = strpos($line, $basepath);  
        if ($pos !== false) { 
            $sline = substr($line, $pos+strlen($basepath)+1); 
            if (!isset($files[$sline])) { 
                $files[$sline] = array('version' => $rev, 'file' => $sline); 
            }         
        } 
    } 

    return array($revs, $files); 
} 


$svn_url = $argv[1]; 
$project = $argv[2]; 
$branch = $argv[3]; 


$base_branch = "{$project}/branches/{$branch}"; 
$base_trunk = "{$project}/trunk"; 
$trunk_url = "{$svn_url}{$base_trunk}"; 
$branch_url = "{$svn_url}{$base_branch}"; 
$cmd = "svn log --verbose --stop-on-copy {$branch_url}";  

$all_revs = false; 

if (isset($argv[4])) 
    $all_revs = true; 

$latest_rev = null; 

list($revs, $files_branch) = svn_data($cmd, $base_branch); 
if ($all_revs) 
    $latest_rev = $revs[count($revs)-1]; 
else 
    $latest_rev = $revs[0]; 

//now check agains main trunk 
$cmd = "svn log --verbose -r {$latest_rev}:HEAD {$trunk_url}"; 
list($revs, $files_trunk) = svn_data($cmd, $base_trunk); 

foreach($files_trunk as $ff => $data) { 
    if (isset($files_branch[$ff])) { 
        print "Changed: {$ff} [REV: {$data['version']}]\n"; 
        $last_version = $files_branch[$ff]['version'];  
        $cmd = "svn diff -r {$last_version}:HEAD {$trunk_url}/{$ff}"; 
        system($cmd); 
    } 
} 