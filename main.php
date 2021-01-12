<?php
header('Content-type: application/json');

include_once __DIR__ . '/Functions/pagination.php';
include_once __DIR__ . '/Functions/Routes.php';



class index extends Pagination
{

    private $connect = ['127.0.0.1', 'root', '', 'rest_api'];

    public function viewPosts(int $page = 1)
    {
        $connect = new mysqli($this->connect[0], $this->connect[1], $this->connect[2], $this->connect[3]);

        $this->getPagination($connect, 6, 'posts', $page);

        $jsonArray = array();
        $jsonArray[]['pagination'] = $this->pagination;


        while ($post = $this->query->fetch_assoc())
        {
            $jsonArray[] = [
                'id' => (int) $post['id'],
                'title' => $post['title'],
                'category' => $post['category'],
                'query' => mb_substr($post['query'], 0, 200) . "..."
            ];
        }

        echo json_encode($jsonArray);
        $connect->close();

    }


    public function viewPost(int $id)
    {
        $connect = new mysqli($this->connect[0], $this->connect[1], $this->connect[2], $this->connect[3]);

        $post = $connect->query("SELECT * FROM `posts` WHERE `id` = {$id}")->fetch_assoc();

        $jsonArray['post'] = [
            'post_id' => (int) $post['id'],
            'datetime' => $post['datetime'],
            'title' => $post['title'],
            'category' => $post['category'],
            'query' => $post['query']
        ];

        echo json_encode($jsonArray);
        $connect->close();

    }


}



Routes::route('/', function ()
{
    $new = new index();
    $new->viewPosts();
});

Routes::route('/page/(\w+)', function (int $page)
{
    $new = new index();
    $new->viewPosts($page);
});

Routes::route('/post/(\w+)', function (int $id)
{
    $new = new index();
    $new->viewPost($id);
});

Routes::execute($_SERVER['REQUEST_URI']);
