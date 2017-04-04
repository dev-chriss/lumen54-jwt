<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Transformers\PostTransformer;
use App\Models\User;

class PostController extends BaseController
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post->orderBy('updated_at', 'desc');
    }

    public function index()
    {
        $posts = $this->post->paginate();
        $posts->map(function ($post) {
            $user =  User::find($post->user_id);
            if ($user) {
              $post['author'] = $user->name;
            }
            else {
              $post['author'] = 'undefined';
            }
            return $post;
        });
        return $this->response->paginator($posts, new PostTransformer());
    }

    /**
     * @api {get} /user/posts (my post list)
     * @apiDescription (my post list)
     * @apiGroup Post
     * @apiPermission none
     * @apiParam {String='comments:limit(x)'} [include]  include
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "user_id": 3,
     *         "title": "foo",
     *         "content": "",
     *         "created_at": "2016-03-30 15:36:30",
     *         "user": {
     *           "data": {
     *             "id": 3,
     *             "email": "foo@bar.com1",
     *             "name": "",
     *             "avatar": "",
     *             "created_at": "2016-03-30 15:34:01",
     *             "updated_at": "2016-03-30 15:34:01",
     *             "deleted_at": null
     *           }
     *         },
     *         "comments": {
     *           "data": [],
     *           "meta": {
     *             "total": 0
     *           }
     *         }
     *       }
     *     ],
     *     "meta": {
     *       "pagination": {
     *         "total": 2,
     *         "count": 2,
     *         "per_page": 15,
     *         "current_page": 1,
     *         "total_pages": 1,
     *         "links": []
     *       }
     *     }
     *   }
     */
    public function userIndex()
    {
        $posts = $this->post
            ->where(['user_id' => $this->user()->id])
            ->paginate();

        return $this->response->paginator($posts, new PostTransformer());
    }

    /**
     * @api {get} /posts/{id} (post detail)
     * @apiDescription (post detail)
     * @apiGroup Post
     * @apiPermission none
     * @apiParam {String='comments','user'} [include]  include
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "data": {
     *       "id": 1,
     *       "user_id": 3,
     *       "title": "foo",
     *       "content": "",
     *       "created_at": "2016-03-30 15:36:30",
     *       "user": {
     *         "data": {
     *           "id": 3,
     *           "email": "foo@bar.com1",
     *           "name": "",
     *           "avatar": "",
     *           "created_at": "2016-03-30 15:34:01",
     *           "updated_at": "2016-03-30 15:34:01",
     *           "deleted_at": null
     *         }
     *       },
     *       "comments": {
     *         "data": [
     *           {
     *             "id": 1,
     *             "post_id": 1,
     *             "user_id": 1,
     *             "reply_user_id": 0,
     *             "content": "foobar",
     *             "created_at": "2016-04-06 14:51:34"
     *           }
     *         ],
     *         "meta": {
     *           "total": 1
     *         }
     *       }
     *     }
     *   }
     */
    public function show($id)
    {
        $post = $this->post->find($id);

        if (! $post) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($post, new PostTransformer());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'title' => 'required|max:50',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $post = new Post;
        $post->title = $request->title;
        $post->content = $request->content;
        $post->user_id = $this->user()->id;
        $post->created_at = \Carbon\Carbon::now('Asia/Jakarta');
        $post->updated_at = \Carbon\Carbon::now('Asia/Jakarta');
        $post->save();

        $user =  User::find($post->user_id);
        if ($user) {
          $post['author'] = $user->name;
        }
        else {
          $post['author'] = 'undefined';
        }

        return $this->response->item($post, new PostTransformer());
    }

    public function update($id, Request $request)
    {
        $post = $this->post->find($id);

        if (! $post) {
            return $this->response->errorNotFound();
        }

        // forbidden
        if ($this->user()->role == 'user' ) {
          if ($post->user_id != $this->user()->id) {
              return $this->response->error('403 forbidden. Only owner can update this post.', 403);
          }
        }

        $validator = \Validator::make($request->input(), [
            'title' => 'required|min:3|max:50',
            'content' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $post->updated_at = \Carbon\Carbon::now('Asia/Jakarta');
        $post->title = $request->title;
        $post->content = $request->content;
        $post->save();

        $user =  User::find($post->user_id);
        if ($user) {
          $post['author'] = $user->name;
        }
        else {
          $post['author'] = 'undefined';
        }

        return $this->response->item($post, new PostTransformer());
    }

    public function destroy($id)
    {
        $post = $this->post->find($id);

        if (! $post) {
            return $this->response->errorNotFound();
        }

        // forbidden
        if ($this->user()->role == 'user' ) {
          if ($post->user_id != $this->user()->id) {
              return $this->response->error('403 forbidden. Only owner can delete this post.', 403);
          }
        }

        $post->forceDelete();

        $user =  User::find($post->user_id);
        if ($user) {
          $post['author'] = $user->name;
        }
        else {
          $post['author'] = 'undefined';
        }
        return $this->response->item($post, new PostTransformer());
    }
}
