<!DOCTYPE html>
@extends('backend.layouts.app')

@section('page-title', trans('app.users'))
@section('page-heading', trans('app.users'))

@section('content')
	<section class="content-header">
	@include('backend.partials.messages')
	</section>

	<section class="content">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div id="main-content" class="for-print">            
        <div id="modal-create" class="modal hide fade" aria-hidden="true" style="display: none;">
            <form id="uploadForm" action="" enctype="multipart/form-data" method="post">
                @csrf
                <div class="pt-5 align-items-center">
                    <div class="dot col-4 align-items-center pt-5 pb-5" id="dropzone" style="background-size: contain;">                                                
                        <input type="file" class="form-control-file @error('image') is-invalid @enderror" style="display: none" id="post_video" name="video">    
                    </div>
                    <div class="d-flex">
                        <button type="button" class="btn-link" id="selectImage">Select a video</a>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <input class="btn btn-success" type="button" value="  Add  " onclick="addVideo()"> 
                    <input class="btn" data-dismiss="modal" aria-hidden="true" type="button" value="Cancel">
                </div>
            </form>
        </div>

        @if(auth()->user()->hasRole(['admin']))
        <div class="row">
            <p>
                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modal-create">
                <i class="icon-plus icon-white"></i> Add video </button>
            </p>
        </div>
        @endif
        <div class="row">
                @foreach($posts as $post)
                <div class="column">
                    <video type="video/mp4" controls="true" style="width: 100%">
                        <source src="{{$post->url}}">
                    </video>
                    @if(auth()->user()->hasRole(['admin']))
                    <button type="button" class="btn btn-warning" style="margin-top: 2px;"  onclick="deletePost('{{$post->Id}}')">
                        <i class="gg-trash"></i>
                    </button>
                    @endif
                    <a href="{{$post->url}}"  class="btn btn-success" style="margin-top: 2px" download>
                        <i class="gg-software-download"></i>                        
                    </a>
                </div>
                
                @endforeach
            </div>

            <!-- The expanding image container -->
            <div class="gallery_container">
            <!-- Close the image -->
            <span onclick="this.parentElement.style.display='none'" class="closebtn">&times;</span>

            <!-- Expanded image -->
            <img id="expandedImg" style="width:100%">

            <!-- Image text -->
            <div id="imgtext"></div>
            </div>
        </div>
	</section>	
@stop

@section('scripts')
	<script>
        var form;
        var file;
        
        window.addEventListener('load', function(){
            form = document.getElementById("dropzone");
            ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (event) {
                
                form.addEventListener(event, function (e) {
                    // preventing the unwanted behaviours
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            ['dragenter'].forEach(function (event) {
                form.addEventListener(event, function () {
                    form.classList.add('dragover');
                });            

                form.addEventListener('drop', function (e) {
                    var droppedFiles;
                    droppedFiles = e.dataTransfer.files; // the files that were dropped

                    if (droppedFiles.length > 0)
                    {
                        file = droppedFiles[0];
                        $(".thumb").remove();
                        var blob = window.URL || window.webkitURL;
                        var sourceUrl = blob.createObjectURL(file);
                        var div = '<div class="thumb"  style="height:100%; position:relative;"> \
                                    <video id="video" type="video/mp4" controls="true"></video> \
                                    <div class="close" style="cursor:pointer">X</div> \
                                </div>';                        
                        $("#dropzone").append(div);
                        $("#video").attr("src", sourceUrl);
                        $("#video").css("width", "100%");
                        $("#video").css("height", "100%");
                        $("video").on("click", function(e){
                            if(this.paused)
                                this.play();
                            else
                                this.pause();
                        });
                    }
                });
            });

            ['dragleave', 'dragend', 'drop'].forEach(function (event) {
                form.addEventListener(event, function () {
                    form.classList.remove('dragover');
                });
            });

            $("#dropzone").on("click", ".close", function(e) {
                var $target = $(e.target);
                var idx = $target.attr('data-idx');
                $target.parent().remove(); //프리뷰 삭제
            });

            $("#selectImage").on("click", function(e){
                $("#post_video").click();
            });

            $("#post_video").change(function(){   
                if(this.files.length == 0)
                    return;
                var file = this.files[0];
                $(".thumb").remove();
                var blob = window.URL || window.webkitURL;
                var sourceUrl = blob.createObjectURL(file);
                var div = '<div class="thumb"  style="height:100%; position:relative;"> \
                            <video id="video" type="video/mp4" controls="true"></video> \
                            <div class="close" style="cursor:pointer">X</div> \
                        </div>';                        
                $("#dropzone").append(div);
                $("#video").attr("src", sourceUrl);
                $("#video").css("width", "100%");
                $("#video").css("height", "100%");
                $("video").on("click", function(e){
                    if(this.paused)
                        this.play();
                    else
                        this.pause();
                });
            });
        });

        function deletePost(id)
        {
            if(confirm("Do you want to really remove this content?"))
            {
                var formData = new FormData();
                formData.append('content_id', id);
                $.ajax({
                    type: 'POST',
                    url: '{!! route("backend.post.delete") !!}',
                    cache: false,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data : formData,
                    success: function(result){
                        console.log(result);
                        var res = JSON.parse(result);
                        if(res.status == "success")
                        {
                            window.location.reload()
                        }
                        else if(res.status == "failure")
                        {
                            alert(res.error);
                        }
                    },
                    error: function(err){
                        console.log(err);
                    }
                });
            }            
        }

        function downloadPost(url)
        {
            window.location.href = url;
        }
        
        function addVideo()
        {
            var files = $("#post_video").prop("files");
            if(files.length > 0)
            {
                var file = files[0];
                var formData = new FormData();
                formData.append('video', file);
                $.ajax({
                    type: 'POST',
                    url: '{!! route("backend.post.addvideo") !!}',
                    cache: false,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data : formData,
                    success: function(result){
                        console.log(result);
                        var res = JSON.parse(result);
                        if(res.status == "success")
                        {
                            window.location.reload()
                        }
                        else if(res.status == "failure")
                        {
                            alert(res.error);
                        }
                    },
                    error: function(err){
                        console.log(err);
                    }
                });
            }
        }
    </script>
@stop
