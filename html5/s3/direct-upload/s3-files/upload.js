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

/**
 * Will upload into S3, as is hosted in the S3 bucket
 * will have enough permission
 */

/* BlobBuilder is an new interface to build a Blob which
 * is required to FormData for sending "files" in a request
 */
BlobBuilder = window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder

//prepare to receive message 
window.addEventListener("message", function(event) {
	//you should check the origin,
	//here is not done for example purposes
	
	var data = event.data;
	
	//upload data through a blob	
	var separator = 'base64,';
	var index = data.content.indexOf(separator);
	if (index != -1) {		
		var bb = new BlobBuilder();
		
		//decode the base64 binary into am ArrayBuffer
		var barray = Base64Binary.decodeArrayBuffer(data.content.substring(index+separator.length));
	    bb.append(barray); 
	
	    var blob = bb.getBlob();
	    
	    //pass post params through FormData 
	    var formdata = new FormData();
	
		for (var param_key in data.params) {
			formdata.append(param_key, data.params[param_key]);
		}
		formdata.append("file", blob, "myblob.png"); //add the blob
	
	
		//finally post the file through AJAX
		var xhr = new XMLHttpRequest();
		xhr.open("POST", data.url, true);  
		xhr.send(formdata);
	}
}, false);
