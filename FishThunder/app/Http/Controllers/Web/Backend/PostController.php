<?php
namespace VanguardLTE\Http\Controllers\Web\Backend
{
    use Illuminate\Http\Request;
    use VanguardLTE\Post;
    use Intervention\Image\Facades\Image;
    class PostController extends \VanguardLTE\Http\Controllers\Controller
    {
        //
        public function __construct()
        {
                        
        }

        public function poster(\Illuminate\Http\Request $request)
        {            
            $posts = Post::where('is_del', '=', '0')->where('type','=','0')->orderBy('id', 'desc')->take(20)->get();
            return view('backend.post.poster', compact('posts'));
        }

        public function video(\Illuminate\Http\Request $request)
        {            
            $posts = Post::where('is_del', '=', '0')->where('type','=','1')->orderBy('id', 'desc')->take(5)->get();
            return view('backend.post.video', compact('posts'));
        }

        public function news(\Illuminate\Http\Request $request)
        {            
            $posts = Post::where('is_del', '=', '0')->where('type','=','2')->orderBy('id', 'desc')->take(1)->get();
            return view('backend.post.news', compact('posts'));
        }

        public function notification(\Illuminate\Http\Request $request)
        {            
            $posts = Post::where('is_del', '=', '0')->where('type','=','3')->orderBy('id', 'desc')->take(1)->get();
            return view('backend.post.notification', compact('posts'));
        }

        public function addposter(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') )) 
            {
                return json_encode(array('status'=>'failure', 'error'=>'invalid authorization'));    
            }
            $data = request()->validate([
                'image' => ['required', 'image'],                
            ]);
            $image_path = '/storage/'.request('image')->store('uploads', 'public');
            Post::create(['type' => '0', 'url' => $image_path]);
            return json_encode(array('status'=>'success'));
        }

        public function addvideo(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') )) 
            {
                return json_encode(array('status'=>'failure', 'error'=>'invalid authorization'));    
            }
            
            $video_path = '/storage/'.request('video')->store('uploads', 'public');
            Post::create(['type' => '1', 'url' => $video_path]);
            return json_encode(array('status'=>'success'));
        }

        public function addnews(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') )) 
            {
                return json_encode(array('status'=>'failure', 'error'=>'invalid authorization'));    
            }

            Post::create(['type' => '2', 'title' => request('title'), 'content' => request('content')]);
            return json_encode(array('status'=>'success'));
        }

        public function addnotification(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') )) 
            {
                return json_encode(array('status'=>'failure', 'error'=>'invalid authorization'));    
            }

            $url = '';
            if($request->hasFile('thumbnail'))
            {
                $destinationPathThumbnail = storage_path('app/public/uploads');
                $image = $request->file('thumbnail');
                $imageName = time().'.'.$image->extension();
                $img = Image::make($image->path());
                $img->resize(512, 512, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPathThumbnail.'/'.$imageName);
                $url = 'https://www.fishthunder.games/storage/uploads/' . $imageName;
            }
            Post::create(['type' => '3', 'title' => request('title'), 'content' => request('content'), 'url' => $url]);
            return json_encode(array('status'=>'success'));
        }

        public function delete(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') )) 
            {
                return json_encode(array('status'=>'failure', 'error'=>'invalid authorization'));    
            }
            $content_id = $request['content_id'];
            
            Post::where("Id", "=", $content_id)->update(['is_del' => '1']);
            return json_encode(array('status'=>'success'));
        }
    }
}
