@extends('project.general.navigation') 

@section('specificScripts')
<script src="/cretin_app_analytics/public/js/SimpleAjaxUploader.js"></script>
<script>
    function escapeTags( str ) {
        return String( str )
                .replace( /&/g, '&amp;' )
                .replace( /"/g, '&quot;' )
                .replace( /'/g, '&#39;' )
                .replace( /</g, '&lt;' )
                .replace( />/g, '&gt;' );
    }

    $(document).ready(function() {

        var btn = document.getElementById('uploadBtn');

        var uploader = new ss.SimpleUpload({
            button: btn,
            url: '/cretin_app_analytics/server.php/upload_logo',
            name: 'image',
            multipart: true,
            hoverClass: 'hover',
            focusClass: 'focus',
            customHeaders: { 
                'X-CSRF-TOKEN': $("#_token").val()
            },
            responseType: 'json',
            allowedExtensions: ["jpg","jpeg","png","gif","bmp"],
            onSubmit: function() {
                $("#uploadBtn").val("アップロードをしてる.....");
                $("#msgBox").text("");
                $("#msgBox").prepend('<img src="/cretin_app_analytics/public/images/loader.gif" id="loader_img" height="30">');    
            },
            onComplete: function( filename, response ) {
                $("#uploadBtn").val("ロゴを変更する");
                $("#loader_img").remove();

                if ( !response ) {
                    msgBox.innerHTML = 'おっとっと! アップロードできない。';
                    return;
                }
                else{                
                    if ( response.error != "1" ){ 
                        msgBox.innerHTML = '<strong>' + escapeTags( filename ) + '</strong>' + ' をアップロードしました。';
                        $("#uploaded_image").remove();
                        $("#msgBox").append('<img id="uploaded_image" height=100 src="/cretin_app_analytics/public/uploads/'+response.filename+'">');
                    }
                    else   
                        msgBox.innerHTML = 'おっとっと! アップロードできない。';
                }
            },
            onError: function() {
                msgBox.innerHTML = 'おっとっと! アップロードできない。';
            }
        });
    });
</script>
@endsection

@section('content')
<style>
    div.control{
        margin-top: 15px;
    }
    #reset {
        margin-left:10px;
    }
    #uploaded_image{
        margin-top:10px;
    }
</style>
<link rel="stylesheet" href=" https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"/>

    @if (count($errors) > 0)
        <div class="align-center" style="margin-top:10px; color:#B71C1C;">
            <strong>おっとっと!</strong> リクエスト処理中に問題が発生しました.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (Session::get('success'))
        <div class="align-center" style="margin-top:10px; color:#004D40;">
            {{ Session::get('success') }}
        </div>
    @endif
    <form id="upload_form" class="ink-form ink-formvalidator" method="post" enctype="multipart/form-data" action="addApp">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="_token">

        <div class="control-group required">

            <div class="control">
                <input type="text" placeholder="アプリの名前" data-rules="required|text[true,false]" name="name" id="app_name" />
            </div>
        </div>
        <div class="control-group required">
            <div class="control">
                <input type="text" placeholder="パッケージの名前" name="package_name" id="ID" data-rules="required" style="width:626px;" />
                <select id="option" name="type">
                    <option value="1">iOS</option>
                    <option value="2">アンドロイド</option>
                </select>
            </div>
        </div>

        <div class="control-group required">
            <div class="control">
                <input type="url" placeholder="ストア　リンク" name="store_link" id="store_url" data-rules="required" />
            </div>
        </div>

        <div class="control-group">
            <div class="control">
                <input type="button" value="ロゴの　画像をアップロードする" id="uploadBtn" style="width:300px;" accept="image/*">
                <br>
                <div id="msgBox" style="display:inline-block;"></div>
                @if( !empty($logo_src) )
                    <img id="uploaded_image" height=100 src="/cretin_app_analytics/public/uploads/{{$logo_src}}">
                @endif
            </div>
        </div>
        <div class="control-group">

            <div class="control">
                <textarea type="text" name="desc" id="desc" >説明
                </textarea>
            </div>
        </div>
        <div class="button-group">
            <input type="submit" name="sub" value="アプリを加える" class="ink-button black push-right" />
            <input type="reset" name="sub" id="reset" value="リセットする" class="ink-button black push-right" />
        </div>
        <a href="/cretin_app_analytics/server.php/viewapps">
            <input type="button" name="sub" id="applist" value="アプリのリスト" class="ink-button red push-left" />
        </a>
    </form>

@endsection