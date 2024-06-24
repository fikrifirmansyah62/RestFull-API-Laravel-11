<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all posts
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    // Store data to Database
    public function store(Request $request)
    {
        // difine validation rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // dd($post);


        return new PostResource(true, 'Data Post berhasil ditambahkan!', $post);
    }

    // Show Data
    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true, 'Detail Data Posts!', $post);
    }

    // Update Data to Database
    public function update(Request $request, $id)
    {
        // difine validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // Check validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // find post by id
        $post = Post::find($id);

        // check if image is not empty
        if ($request->hasFile('image')) {
            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // delete old image
            Storage::delete('public/posts/' . basename($post->image));

            // update post with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            // update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        // return response
        return new PostResource(true, 'Data Post berhasil diupdate!', $post);
    }

    // Delete Data in Database
    public function destroy($id)
    {
        // find post by id
        $post = Post::find($id);

        // delete image
        Storage::delete('public/posts/' . basename($post->image));

        // delete post
        $post->delete();

        // return response
        return new PostResource(true, 'Data Post berhasil dihapus!', null);
    }
}
