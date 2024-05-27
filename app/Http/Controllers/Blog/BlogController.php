<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Blog;
use App\Models\User;
use App\Notifications\NewBlogNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    //create blogs
    public function blog(Request $request)
    {
        // dd($request->isMethod('delete'));
        if ($request->isMethod('post')) {
            // dd(Auth::user()->id);
            // check if the images is present and a method used
            $method = $request->isMethod("post");
            if ($method) {
                // validate the incoming request data
                $validateBlog = Validator(
                    $request->all(),
                    [
                        'image' => 'required|mimes:png,jpg,jpeg|max:2048',
                        'title' => 'required|string',
                        'content' => 'required|string',
                    ]
                );
                //validation failed
                if($validateBlog->fails()){
                    return response()->json([
                        'status'=>false,
                        'message'=>"Validation failed!",
                        'errors'=>$validateBlog->errors(),
                    ], 401);
                }

                //success we need to check the files if present
                if($request->hasFile('image')){
                    //stored the image and get the path
                    $path = $request->file('image')->store('blogs', 'public');
                }

                //stored on database
                $blog = Blog::create([
                    'user_id'=>Auth::user()->id,
                    'image'=>$path,
                    'title'=>$request->title,
                    'content'=>$request->content,
                ]);

                if($blog){
                    $usersToNotify = User::all();
                    $details = [
                        'subject' => "Notification: ".Auth::user()->name." has a new Blog Post.",
                        'body' => "Newly created blog post is now available.",
                        'action' => route('userblog')
                    ];
                    foreach ($usersToNotify as $user) {
                       $this->sendEmailNotificationToAll($user->id, $details);
                    }

                    // stored the audit logs
                    AuditLog::create([
                        'blog_id' =>$blog->id,
                        'action' => 'create',
                        'new_values' => json_encode($blog)
                    ]);
                }

                return response()->json([
                    'status'=>true,
                    'message'=>"Blog successfully created!",
                    'blog'=>$blog
                ], 200);
            }
        } else {

            // getting all the blogs using relationship
            // reverse relation
            // $blogs = User::with('blogs')->->get();
            $blogs = Blog::with(['user'=> function($query){
                $query->select('id','name');
            }])->latest()->get();
            // dd($blogs);

            return response()->json([
                'status' => true,
                'message' => "Blogs Available!",
                'blogs' => $blogs,
            ], 200);

        }
    }

    //update blog
    public function update(Request $request){
        // dd($request);
        $validateBlog = Validator::make($request->all(),
            [
                'image'=>'mimes:png,jpg,jpeg|max:2048',
                'title'=>'required|string',
                'content'=>'required|string',
            ]
        );

        if($validateBlog->fails()){
            return response()->json([
                'status'=>false,
                'message'=>'Validation failed!',
                'errors'=>$validateBlog->errors(),
            ], 401);
        }

        $hasImage = false;
        if($request->hasFile('image')){
            $hasImage = true;
            $path = $request->file('image')->store('blogs', 'public');
        }

        $blog = Blog::find($request->id);
        $oldValues = $blog->toArray();
        // dd($oldValues);
        $dataToUpdate = [
            'title' => $request->title,
            'content' => $request->content,
        ];

        //check if there is  new image
        if($hasImage){
            $dataToUpdate['image'] = $path;
        }

        $blog->update($dataToUpdate);

        $updatedBlogs = Blog::find($request->id);//this fetches the latest data
        // stored the audit logs
        AuditLog::create([
            'blog_id' =>$blog->id,
            'action' => 'update',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($updatedBlogs->getAttributes()),
            
        ]);

        $hasImage = false;
        
        return response()->json([
            'status'=>true,
            'message'=>"Successfully Updated!",
            'blog' => Blog::find($request->id),
        ], 200);

        // Blog::find($request->id)->update([
        //     'title'=>$request->title,
        //     'content'=>$request->content,
        // ]);


    }

    //delete blogs based on id
    public function delete(Request $request){
        // dd($request->id);
        $blog = Blog::findOrFail($request->id);

        // capture the values before deletion
        $oldValues = $blog->toArray();
        AuditLog::create([
            'blog_id' =>$blog->id,
            'action' => 'delete',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode(null),
            
        ]);

        $blog->delete();

        return response()->json([
            'status'=>true,
            'message'=>"Successfully Deleted!",
            'blog'=>[],
        ], 200);
    }

    //send notification
    private function sendEmailNotificationToAll($notifyId, $details){
        $notifyAll = User::where('id', $notifyId)->first();
        Notification::send($notifyAll, new NewBlogNotification($details));
    }
}
